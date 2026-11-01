<?php

namespace App\Support;

use DateTime;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Generator data dummy transaksi (penjualan, pembelian, biaya operasional) untuk periode
 * tertentu, berdasarkan pola data historis dan HANYA memakai data master yang sudah ada.
 * Aturan lengkap ada di database/dummy/README.md.
 *
 * Hasilnya berupa file .sql (satu transaksi, gagal = rollback semua) yang bisa dieksekusi
 * lewat execute() / `php artisan dummy:execute`, atau di-copy manual ke SQL Server.
 *
 * Inti simulasinya:
 *   0. Rencana per hari dibuat di awal: target omzet (pola mingguan, gajian, tren, hari sepi/ramai,
 *      acak harian) dan jenis hari pembelian (belanja besar / biasa / Minggu). Rata-rata omzet
 *      per bulan dijaga dalam ±BATAS_BULANAN dari acuan; harian boleh naik-turun bebas.
 *   1. Pagi: pembelian untuk barang yang stoknya menipis (dikelompokkan per supplier).
 *   2. Penjualan: nota dibuat sampai omzet mencapai target hari itu,
 *      barang hanya bisa dipilih kalau stoknya cukup -> stok tidak pernah minus.
 *   3. Jam nota yang butuh stok dari pembelian hari itu digeser ke setelah jam pembelian.
 *   4. Biaya operasional (tidak setiap hari) mengikuti pola nominal & keterangan historis.
 */
class DummyTransaksiGenerator
{
    const PREFIX = 'HM';
    const KD_DIVISI = 'DAA000';
    const KD_JENIS = 'JAA000';
    const KD_KAS = 'KAA000';
    const KD_VOUCHER = 'VAA000';
    const KD_PEGAWAI = 'PAA000';
    const KD_BIAYA_LISTRIK = 'BAA004';
    // m_barang.status yang ikut ditransaksikan. Status 2 tampil "Tidak Aktif" di UI tetapi masih laku
    // (gas 3kg, rokok, dll ~1/3 omzet historis), status 0 tidak dipakai.
    const STATUS_BARANG = '1, 2';

    // kd_user dari m_user: kasir untuk penjualan (shift pagi / sore), admin untuk pembelian & biaya
    const USER_KASIR_PAGI = 'UAA003';
    const USER_KASIR_SORE = 'UAA004';
    const USER_ADMIN = ['UAA001', 'UAA002'];

    const WINDOW_OMZET_HARI = 30;   // acuan rata-rata omzet & jumlah nota harian
    const WINDOW_POLA_HARI = 30;    // acuan bobot barang, laju penjualan, qty
    // ---- pola omzet harian (faktor x rata-rata acuan) ----
    const FAKTOR_HARI = [1 => 1.05, 2 => 1.00, 3 => 0.88, 4 => 0.90, 5 => 1.00, 6 => 1.10, 7 => 1.12]; // Senin..Minggu
    const FAKTOR_GAJIAN = [3 => 1.15, 7 => 1.06, 14 => 1.00, 24 => 0.93, 27 => 1.00, 31 => 1.08]; // s/d tanggal => faktor
    const ACAK_HARIAN = 0.065;      // simpangan acak harian (normal), dipotong ±20%
    const TREN_MINGGUAN = 0.03;     // rata-rata tiap minggu bergeser acak ±3% dan cenderung kembali ke acuan...
    const BATAS_TREN = 0.06;        // ...dengan total maksimal ±6% dari acuan
    const PELUANG_SEPI = 0.067;     // ~2x per bulan, omzet x0.55-0.75
    const PELUANG_RAMAI = 0.067;    // ~2x per bulan, omzet x1.20-1.35
    const HARI_SPESIAL = ['08-17' => [1.25, 1.35, '17 Agustus']]; // m-d => [faktor min, max, keterangan]
    const BATAS_BULANAN = 0.06;     // rencana rata-rata per bulan dijaga ±6% (aturan: hasil ±10%)

    // ---- pola pembelian per jenis hari ----
    // ambang: barang ikut dibeli kalau stok < ambang hari penjualan; cover: stok setelah beli (hari);
    // nota: jumlah nota (supplier) per hari, bisa lebih kalau ada barang yang stoknya < REORDER_HARI
    const MODE_BELI = [
        'besar'  => ['ambang' => 12, 'cover' => [18, 24], 'nota' => [15, 25], 'maks_wajib' => 25], // kulakan 1-2x/minggu
        'biasa'  => ['ambang' => 9, 'cover' => [12, 16], 'nota' => [5, 11], 'maks_wajib' => 14],   // restock kecil
        'minggu' => ['ambang' => 1.5, 'cover' => [7, 10], 'nota' => [0, 1], 'maks_wajib' => 2],    // hanya yang kritis
    ];
    const REORDER_HARI = 4;         // barang dengan stok < 4 hari penjualan wajib dibeli
    const MAX_ITEM_PER_NOTA_BELI = 20;
    const BARIS_PER_BATCH = 500;    // baris per INSERT / batch SQL (kecil = hemat memori SQL Server)
    const BATAS_ATAS_NOTA = 1.02;   // nota terakhir boleh melewati target max 2%
    const HARI_LISTRIK = 5;         // tanggal bayar listrik tiap bulan

    private $log;

    // ---- data acuan (dimuat dari DB) ----
    private $baseOmzet = 0;
    private $baseNota = 0;
    private $baseBeli = 0;
    private $jamBobot = [];       // jam => bobot
    private $itemPerNota = [];    // jumlah item => bobot
    private $customerBobot = [];  // kd_customer => bobot
    private $barangJual = [];     // "kd_barang|kd_satuan" => [kd_barang, kd_satuan, harga_jual, jumlah]
    private $dijual = [];         // kd_barang => true (punya satuan jual aktif)
    private $barangKum = [];      // cumulative weight untuk pemilihan barang+satuan
    private $barangKode = [];
    private $barangTotal = 0;
    private $qtySampel = [];      // "kd_barang|kd_satuan" => [qty, ...]
    private $laju = [];           // kd_barang => rata-rata terjual per hari (satuan terkecil), dari histori
    private $lajuAktual = [];     // kd_barang => rata-rata bergerak penjualan hasil simulasi
    private $terjualHariIni = []; // kd_barang => qty terjual hari ini (satuan terkecil)
    private $infoBeli = [];       // kd_barang => [kd_supplier, kd_satuan, jumlah, harga_beli]
    private $hargaBeliUnit = [];  // kd_barang => harga beli terakhir per satuan terkecil
    private $stok = [];           // kd_barang => stok berjalan (satuan terkecil)
    private $biayaSampel = [];    // kd_biaya => [[nominal, keterangan], ...]
    private $biayaBobot = [];     // kd_biaya => bobot (tanpa listrik)
    private $biayaPerHari = [];   // jumlah transaksi biaya per hari => bobot
    private $biayaPeluang = 0.8;  // peluang ada biaya di suatu hari
    private $seqAwal = [];        // tabel => [yymmdd => seq terakhir yang sudah ada]
    private $guardBatch = '';     // baris pembuka tiap batch SQL: berhenti kalau transaksi sudah di-rollback
    private $rencana = [];        // Y-m-d => ['faktor' => x omzet acuan, 'ket' => string, 'beli' => key MODE_BELI]

    // ---- hasil ----
    private $rowsJual = [];
    private $rowsJualDetail = [];
    private $rowsBeli = [];
    private $rowsBeliDetail = [];
    private $rowsBiaya = [];
    private $ringkasan = [];

    /**
     * @param callable|null $log fungsi(string $pesan) untuk menampilkan progres
     */
    public function __construct(callable $log = null)
    {
        $this->log = $log ?: function ($m) {
        };
    }

    /**
     * Generate file SQL insert data dummy.
     *
     * @param string|null $from   Y-m-d, default = tanggal penjualan terakhir + 1 hari
     * @param string|null $to     Y-m-d, default = hari ini (dibuat 1 hari penuh)
     * @param int|null    $seed   seed random agar hasil bisa diulang persis
     * @param string|null $outDir folder output, default database/dummy/output
     * @param bool        $sampaiJamSekarang true = transaksi hari ini hanya s/d jam sekarang
     * @return array ['file' => path, 'from' =>, 'to' =>, 'seed' =>, 'ringkasan' => [...], 'total' => [...]]
     */
    public function generate($from = null, $to = null, $seed = null, $outDir = null, $sampaiJamSekarang = false)
    {
        $last = DB::selectOne("SELECT CONVERT(varchar(10), MAX(tanggal), 120) AS d FROM t_penjualan")->d;
        $from = $from ?: date('Y-m-d', strtotime($last . ' +1 day'));
        $to = $to ?: date('Y-m-d');
        if ($from > $to) {
            throw new RuntimeException("Tanggal mulai ($from) melewati tanggal akhir ($to). Tidak ada yang perlu di-generate.");
        }
        $sudahAda = DB::selectOne(
            "SELECT COUNT(*) AS c FROM t_penjualan WHERE tanggal >= ? AND tanggal < DATEADD(DAY, 1, CAST(? AS DATE))",
            [$from, $to]
        )->c;
        if ($sudahAda > 0) {
            throw new RuntimeException("Sudah ada $sudahAda penjualan di periode $from s/d $to. Pakai --from setelah tanggal penjualan terakhir ($last).");
        }

        $seed = $seed !== null ? (int) $seed : mt_rand(1, 2147483647);
        mt_srand($seed);

        ($this->log)("Periode $from s/d $to, seed $seed");
        $this->muatAcuan($from, $to);
        ($this->log)(sprintf(
            'Acuan: omzet %s/hari, %.0f nota/hari, %.1f pembelian/hari, %d barang dijual, %d barang bisa dibeli',
            number_format($this->baseOmzet, 0, ',', '.'), $this->baseNota, $this->baseBeli,
            count($this->barangKode), count($this->infoBeli)
        ));
        $this->rencanaHarian($from, $to);

        $now = new DateTime();
        $hari = new DateTime($from);
        $akhir = new DateTime($to);
        while ($hari <= $akhir) {
            $tgl = $hari->format('Y-m-d');
            $batasDetik = 86399;
            if ($sampaiJamSekarang && $tgl === $now->format('Y-m-d')) {
                $batasDetik = (int) $now->format('H') * 3600 + (int) $now->format('i') * 60 + (int) $now->format('s');
            }
            $this->simulasiHari($tgl, $batasDetik);
            $hari->modify('+1 day');
        }

        $outDir = $outDir ?: base_path('database/dummy/output');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }
        $file = $outDir . DIRECTORY_SEPARATOR . sprintf(
            'dummy_transaksi_%s_%s_%s.sql',
            str_replace('-', '', $from), str_replace('-', '', $to), date('YmdHis')
        );
        $this->tulisSql($file, $from, $to, $seed);

        return [
            'file' => $file,
            'from' => $from,
            'to' => $to,
            'seed' => $seed,
            'ringkasan' => $this->ringkasan,
            'bulanan' => $this->ringkasanBulanan(),
            'total' => [
                't_penjualan' => count($this->rowsJual),
                't_penjualan_detail' => count($this->rowsJualDetail),
                't_pembelian' => count($this->rowsBeli),
                't_pembelian_detail' => count($this->rowsBeliDetail),
                't_biaya_operasional' => count($this->rowsBiaya),
            ],
        ];
    }

    /**
     * Eksekusi file SQL hasil generate(), lalu (opsional) jalankan SP turunan:
     * sp_harga_beli_terakhir, sp_Generate_Jurnal_Umum (periode file), sp_GenerateHutangPPhFinal.
     *
     * @return array ['from' =>, 'to' =>]
     */
    public function execute($file, $jalankanSp = true)
    {
        if (!is_file($file)) {
            throw new RuntimeException("File tidak ditemukan: $file");
        }
        $sql = file_get_contents($file);
        if (!preg_match('/^-- @periode: (\d{4}-\d{2}-\d{2}) (\d{4}-\d{2}-\d{2})/m', $sql, $m)) {
            throw new RuntimeException('File bukan hasil dummy:generate (header @periode tidak ditemukan).');
        }
        list(, $from, $to) = $m;

        // Bagian SP di file dijalankan terpisah per statement agar log & error per SP jelas
        $sql = preg_replace('/^-- @sp-mulai$.*?^-- @sp-selesai$/ms', '', $sql);

        // Jalankan batch demi batch (pemisah GO) di koneksi yang sama; transaksi dibuka di batch pertama
        // dan di-commit di batch terakhir. Gagal di mana pun -> rollback semua.
        // Blok komentar /* ... */ (query rollback manual) dibuang agar tidak ikut dieksekusi
        $sql = preg_replace('~/\*.*?\*/~s', '', $sql);
        $batch = array_values(array_filter(preg_split('/^GO\s*$/m', $sql), function ($b) {
            return trim(preg_replace('/^--.*$/m', '', $b)) !== '';
        }));
        ($this->log)('Insert data (' . count($batch) . ' batch) ...');
        try {
            foreach ($batch as $b) {
                DB::unprepared($b);
            }
        } catch (\Throwable $e) {
            if ((int) DB::selectOne('SELECT @@TRANCOUNT AS c')->c > 0) {
                DB::unprepared('ROLLBACK TRANSACTION');
            }
            throw $e;
        }

        if ($jalankanSp) {
            foreach (self::perintahSp($from, $to) as $sp) {
                ($this->log)("$sp ...");
                DB::statement($sp);
            }
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Perintah SP turunan setelah insert. sp_GenerateHutangPPhFinal @Tanggal memproses semua bulan
     * SEBELUM bulan @Tanggal (bulan yang sudah ada dilewati), jadi dipanggil per tgl 1 bulan sampai
     * bulan dari ($to + 1 hari): kalau $to akhir bulan, bulan itu sudah penuh dan ikut dibuat PPh-nya.
     */
    public static function perintahSp($from, $to)
    {
        // Jurnal dibuat ulang sejak transaksi pertama: HPP memakai harga beli rata-rata s/d tanggal akhir,
        // jadi jurnal lama ikut dihitung ulang agar neraca (akun 103) = rincian persediaan barang.
        $awal = DB::selectOne("SELECT CONVERT(varchar(10), MIN(t), 120) AS d FROM (
                                   SELECT MIN(tanggal) AS t FROM t_penjualan
                                   UNION ALL SELECT MIN(tanggal) FROM t_pembelian
                                   UNION ALL SELECT MIN(tanggal) FROM t_biaya_operasional
                                   UNION ALL SELECT MIN(tanggal) FROM t_pendapatan) x")->d;
        $awal = $awal && $awal < $from ? $awal : $from;
        $sp = [
            'EXEC sp_harga_beli_terakhir;',
            "EXEC sp_Generate_Jurnal_Umum '$awal', '$to';",
        ];
        foreach (self::bulanPeriode($from, date('Y-m-d', strtotime("$to +1 day"))) as $bulan) {
            $sp[] = "EXEC sp_GenerateHutangPPhFinal '$bulan';";
        }
        return $sp;
    }

    /** Tanggal 1 tiap bulan yang tersentuh periode $from..$to. */
    private static function bulanPeriode($from, $to)
    {
        $hasil = [];
        for ($bulan = date('Y-m-01', strtotime($from)); $bulan <= $to; $bulan = date('Y-m-01', strtotime("$bulan +1 month"))) {
            $hasil[] = $bulan;
        }
        return $hasil;
    }

    // =====================================================================================
    // Pemuatan data acuan
    // =====================================================================================

    private function muatAcuan($from, $to)
    {
        $omzetDari = date('Y-m-d', strtotime("$from -" . self::WINDOW_OMZET_HARI . ' days'));
        $polaDari = date('Y-m-d', strtotime("$from -" . self::WINDOW_POLA_HARI . ' days'));

        // Omzet & jumlah nota harian. Hari yang tidak penuh (nota < 50% median) diabaikan.
        $harian = DB::select("SELECT CAST(p.tanggal AS DATE) AS d, COUNT(DISTINCT p.no_transaksi) AS c,
                                     SUM(d.harga_jual * d.qty) AS omzet
                              FROM t_penjualan p
                              JOIN t_penjualan_detail d ON d.no_transaksi = p.no_transaksi
                              WHERE p.tanggal >= ? AND p.tanggal < ?
                              GROUP BY CAST(p.tanggal AS DATE)", [$omzetDari, $from]);
        if (empty($harian)) {
            throw new RuntimeException("Tidak ada penjualan historis di $omzetDari s/d $from untuk acuan.");
        }
        $counts = array_map(function ($r) {
            return (int) $r->c;
        }, $harian);
        sort($counts);
        $median = $counts[intdiv(count($counts), 2)];
        $penuh = array_values(array_filter($harian, function ($r) use ($median) {
            return $r->c >= 0.5 * $median;
        }));
        $this->baseOmzet = array_sum(array_column($penuh, 'omzet')) / count($penuh);
        $this->baseNota = array_sum(array_column($penuh, 'c')) / count($penuh);

        $beli = DB::selectOne("SELECT AVG(c * 1.0) AS avg_c FROM (
                                  SELECT CAST(tanggal AS DATE) AS d, COUNT(*) AS c FROM t_pembelian
                                  WHERE tanggal >= ? AND tanggal < ? GROUP BY CAST(tanggal AS DATE)) x",
            [$omzetDari, $from]);
        $this->baseBeli = $beli->avg_c ? (float) $beli->avg_c : 10;

        foreach (DB::select("SELECT DATEPART(HOUR, tanggal) AS h, COUNT(*) AS c FROM t_penjualan
                             WHERE tanggal >= ? AND tanggal < ? GROUP BY DATEPART(HOUR, tanggal)", [$polaDari, $from]) as $r) {
            $this->jamBobot[(int) $r->h] = (int) $r->c;
        }

        foreach (DB::select("SELECT n, COUNT(*) AS c FROM (
                                SELECT d.no_transaksi, COUNT(*) AS n FROM t_penjualan_detail d
                                JOIN t_penjualan p ON p.no_transaksi = d.no_transaksi
                                WHERE p.tanggal >= ? AND p.tanggal < ? GROUP BY d.no_transaksi) x
                             GROUP BY n", [$polaDari, $from]) as $r) {
            $this->itemPerNota[(int) $r->n] = (int) $r->c;
        }

        foreach (DB::select("SELECT p.kd_customer, COUNT(*) AS c FROM t_penjualan p
                             JOIN m_customer c ON c.kd_customer = p.kd_customer AND c.status = 1
                             WHERE p.tanggal >= ? AND p.tanggal < ? GROUP BY p.kd_customer", [$polaDari, $from]) as $r) {
            $this->customerBobot[$r->kd_customer] = (int) $r->c;
        }
        if (empty($this->customerBobot)) {
            $this->customerBobot = ['CAA000' => 1];
        }

        // Pasangan barang + satuan yang bisa dijual: barang aktif, satuan aktif, harga jual > 0
        foreach (DB::select("SELECT b.kd_barang, mbs.kd_satuan, mbs.harga_jual, mbs.jumlah
                             FROM m_barang b
                             JOIN m_barang_satuan mbs ON mbs.kd_barang = b.kd_barang
                             WHERE b.status IN (" . self::STATUS_BARANG . ") AND mbs.status = 1 AND mbs.jumlah > 0 AND mbs.harga_jual > 0") as $r) {
            $this->barangJual[$r->kd_barang . '|' . $r->kd_satuan] = [$r->kd_barang, $r->kd_satuan, (float) $r->harga_jual, (float) $r->jumlah];
            $this->dijual[$r->kd_barang] = true;
        }

        // Bobot barang+satuan, sampel qty & laju penjualan per hari dari histori
        $bobot = [];
        $terjual = [];
        foreach (DB::select("SELECT d.kd_barang, d.kd_satuan, d.qty, mbs.jumlah
                             FROM t_penjualan_detail d
                             JOIN t_penjualan p ON p.no_transaksi = d.no_transaksi
                             JOIN m_barang_satuan mbs ON mbs.kd_barang = d.kd_barang AND mbs.kd_satuan = d.kd_satuan AND mbs.status = 1
                             WHERE p.tanggal >= ? AND p.tanggal < ? AND d.qty > 0", [$polaDari, $from]) as $r) {
            $terjual[$r->kd_barang] = ($terjual[$r->kd_barang] ?? 0) + $r->qty * $r->jumlah;
            $key = $r->kd_barang . '|' . $r->kd_satuan;
            if (!isset($this->barangJual[$key])) {
                continue;
            }
            $bobot[$key] = ($bobot[$key] ?? 0) + 1;
            $this->qtySampel[$key][] = (float) $r->qty;
        }
        $hariPola = max(1, (strtotime($from) - strtotime($polaDari)) / 86400);
        foreach ($terjual as $kd => $q) {
            $this->laju[$kd] = $q / $hariPola;
        }
        $this->lajuAktual = $this->laju;
        $kum = 0;
        foreach ($bobot as $key => $w) {
            $kum += $w;
            $this->barangKode[] = $key;
            $this->barangKum[] = $kum;
        }
        $this->barangTotal = $kum;

        // Info pembelian terakhir per barang (supplier, satuan, harga) -> acuan pembelian dummy
        foreach (DB::select("SELECT kd_barang, kd_satuan, harga_beli, jumlah, kd_supplier FROM (
                                SELECT d.kd_barang, d.kd_satuan, d.harga_beli, mbs.jumlah, p.kd_supplier,
                                       ROW_NUMBER() OVER (PARTITION BY d.kd_barang ORDER BY p.tanggal DESC, d.nomor DESC) AS rn
                                FROM t_pembelian_detail d
                                JOIN t_pembelian p ON p.no_transaksi = d.no_transaksi
                                JOIN m_barang_satuan mbs ON mbs.kd_barang = d.kd_barang AND mbs.kd_satuan = d.kd_satuan AND mbs.status = 1
                                JOIN m_supplier s ON s.kd_supplier = p.kd_supplier
                                JOIN m_barang b ON b.kd_barang = d.kd_barang AND b.status IN (" . self::STATUS_BARANG . ")
                                WHERE d.jenis = 1 AND d.qty > 0 AND d.harga_beli > 0 AND mbs.jumlah > 0) x
                             WHERE rn = 1") as $r) {
            $this->infoBeli[$r->kd_barang] = [$r->kd_supplier, $r->kd_satuan, (float) $r->jumlah, (float) $r->harga_beli];
            $this->hargaBeliUnit[$r->kd_barang] = $r->harga_beli / $r->jumlah;
        }
        // Harga beli terakhir versi app (sudah memperhitungkan diskon)
        foreach (array_chunk(array_keys($this->dijual), 200) as $chunk) {
            foreach (DB::select("SELECT kd_barang, harga_beli FROM dbo.getHargaBeliTerakhir(?)", [implode(',', $chunk)]) as $r) {
                $this->hargaBeliUnit[$r->kd_barang] = (float) $r->harga_beli;
            }
        }

        foreach (DB::select("SELECT kd_barang, stok FROM v_stok_barang") as $r) {
            $this->stok[$r->kd_barang] = (float) $r->stok;
        }

        // Pola biaya operasional
        $hariBiaya = [];
        foreach (DB::select("SELECT b.kd_biaya, b.nominal, b.keterangan, CONVERT(varchar(10), b.tanggal, 120) AS d
                             FROM t_biaya_operasional b
                             JOIN m_biaya m ON m.kd_biaya = b.kd_biaya AND m.status = 1
                             WHERE b.nominal > 0") as $r) {
            $this->biayaSampel[$r->kd_biaya][] = [(float) $r->nominal, $r->keterangan];
            if ($r->kd_biaya !== self::KD_BIAYA_LISTRIK) {
                $this->biayaBobot[$r->kd_biaya] = ($this->biayaBobot[$r->kd_biaya] ?? 0) + 1;
                $hariBiaya[$r->d] = ($hariBiaya[$r->d] ?? 0) + 1;
            }
        }
        if (!empty($hariBiaya)) {
            $rentang = (strtotime(max(array_keys($hariBiaya))) - strtotime(min(array_keys($hariBiaya)))) / 86400 + 1;
            $this->biayaPeluang = min(1, count($hariBiaya) / $rentang);
            foreach ($hariBiaya as $n) {
                $this->biayaPerHari[$n] = ($this->biayaPerHari[$n] ?? 0) + 1;
            }
        }

        // Nomor urut terakhir yang sudah ada (prefix HM) per hari di periode, supaya tidak bentrok
        foreach (['t_penjualan', 't_pembelian', 't_biaya_operasional'] as $tabel) {
            $this->seqAwal[$tabel] = [];
            foreach (DB::select("SELECT SUBSTRING(no_transaksi, 3, 6) AS ymd, MAX(RIGHT(no_transaksi, 4)) AS seq
                                 FROM $tabel
                                 WHERE no_transaksi LIKE 'HM%' AND LEN(no_transaksi) = 12
                                   AND no_transaksi BETWEEN ? AND ?
                                 GROUP BY SUBSTRING(no_transaksi, 3, 6)",
                [self::PREFIX . date('ymd', strtotime($from)) . '0000', self::PREFIX . date('ymd', strtotime($to)) . '9999']) as $r) {
                $this->seqAwal[$tabel][$r->ymd] = (int) $r->seq;
            }
        }
    }

    // =====================================================================================
    // Rencana per hari
    // =====================================================================================

    /**
     * Target omzet & jenis hari pembelian untuk seluruh periode. Dibuat di awal supaya rata-rata
     * per bulan bisa dijaga dalam ±BATAS_BULANAN dari acuan, sementara harian bebas naik-turun.
     */
    private function rencanaHarian($from, $to)
    {
        $rataHari = array_sum(self::FAKTOR_HARI) / count(self::FAKTOR_HARI);
        $tren = 1.0;
        $belanjaBesar = [];
        for ($tgl = $from; $tgl <= $to; $tgl = date('Y-m-d', strtotime("$tgl +1 day"))) {
            $dow = (int) date('N', strtotime($tgl));
            $minggu = date('o-W', strtotime($tgl));
            if (!isset($belanjaBesar[$minggu])) {
                // Minggu baru: tren bergeser pelan, pilih 1-2 hari belanja besar dari sisa Senin-Sabtu
                $tren = max(1 - self::BATAS_TREN, min(1 + self::BATAS_TREN,
                    1 + 0.7 * ($tren - 1) + $this->randf(-self::TREN_MINGGUAN, self::TREN_MINGGUAN)));
                $sisa = $dow <= 6 ? range($dow, 6) : [];
                shuffle($sisa);
                $belanjaBesar[$minggu] = array_slice($sisa, 0, mt_rand(1, 2));
            }

            $faktor = self::FAKTOR_HARI[$dow] / $rataHari * $this->faktorGajian($tgl) * $tren
                * max(0.8, min(1.2, 1 + self::ACAK_HARIAN * $this->randn()));
            $ket = '';
            $u = $this->randf(0, 1);
            $spesial = self::HARI_SPESIAL[date('m-d', strtotime($tgl))] ?? null;
            if ($spesial) {
                $faktor *= $this->randf($spesial[0], $spesial[1]);
                $ket = $spesial[2];
            } elseif ($u < self::PELUANG_SEPI) {
                $faktor *= $this->randf(0.55, 0.75);
                $ket = 'hari sepi';
            } elseif ($u < self::PELUANG_SEPI + self::PELUANG_RAMAI) {
                $faktor *= $this->randf(1.20, 1.35);
                $ket = 'hari ramai';
            }

            $this->rencana[$tgl] = [
                'faktor' => $faktor,
                'ket' => $ket,
                'beli' => $dow === 7 ? 'minggu' : (in_array($dow, $belanjaBesar[$minggu], true) ? 'besar' : 'biasa'),
            ];
        }

        // Rata-rata per bulan di luar batas -> semua hari di bulan itu diskalakan (pola harian tetap)
        $perBulan = [];
        foreach ($this->rencana as $tgl => $r) {
            $perBulan[substr($tgl, 0, 7)][] = $tgl;
        }
        foreach ($perBulan as $daftar) {
            $rata = 0;
            foreach ($daftar as $tgl) {
                $rata += $this->rencana[$tgl]['faktor'] / count($daftar);
            }
            $skala = max(1 - self::BATAS_BULANAN, min(1 + self::BATAS_BULANAN, $rata)) / $rata;
            foreach ($daftar as $tgl) {
                $this->rencana[$tgl]['faktor'] *= $skala;
            }
        }
    }

    private function faktorGajian($tgl)
    {
        $d = (int) date('j', strtotime($tgl));
        foreach (self::FAKTOR_GAJIAN as $sampai => $f) {
            if ($d <= $sampai) {
                return $f;
            }
        }
        return 1.0;
    }

    // =====================================================================================
    // Simulasi per hari
    // =====================================================================================

    private function simulasiHari($tgl, $batasDetik)
    {
        $stokAwal = $this->stok;
        $kedatangan = $this->simulasiPembelian($tgl, $batasDetik);
        $tersedia = $this->persenTersedia();
        $omzet = $this->simulasiPenjualan($tgl, $batasDetik, $stokAwal, $kedatangan, $target, $jumlahNota);
        $jumlahBiaya = $this->simulasiBiaya($tgl, $batasDetik);

        // Laju aktual (rata-rata bergerak) -> barang yang ternyata lebih laku dibeli lebih banyak
        foreach ($this->lajuAktual as $kd => $l) {
            $this->lajuAktual[$kd] = 0.8 * $l + 0.2 * ($this->terjualHariIni[$kd] ?? 0);
        }
        $this->terjualHariIni = [];

        foreach ($this->stok as $kd => $s) {
            if ($s < -1e-9) {
                throw new RuntimeException("BUG: stok $kd minus ($s) pada $tgl");
            }
        }
        $this->ringkasan[] = [
            'tanggal' => $tgl,
            'nota_jual' => $jumlahNota,
            'omzet' => $omzet,
            'target' => $target,
            'deviasi_pct' => $this->baseOmzet > 0 ? ($omzet / $this->baseOmzet - 1) * 100 : 0,
            'nota_beli' => count($kedatangan['nota']),
            'nilai_beli' => $kedatangan['nilai'],
            'mode_beli' => $this->rencana[$tgl]['beli'],
            'ket' => $this->rencana[$tgl]['ket'],
            'biaya' => $jumlahBiaya,
            'tersedia_pct' => $tersedia,
            'parsial' => $batasDetik < 86399,
        ];
        ($this->log)(sprintf('%s %s  jual %3d nota %10s (%+6.1f%%)  beli %2d nota %10s %-7s biaya %d  stok %.0f%%  %s',
            $tgl, substr(date('D', strtotime($tgl)), 0, 2), $jumlahNota, number_format($omzet, 0, ',', '.'),
            end($this->ringkasan)['deviasi_pct'], count($kedatangan['nota']), number_format($kedatangan['nilai'], 0, ',', '.'),
            $this->rencana[$tgl]['beli'], $jumlahBiaya, $tersedia, $this->rencana[$tgl]['ket']));
    }

    private function lajuEfektif($kd)
    {
        return max($this->laju[$kd] ?? 0, $this->lajuAktual[$kd] ?? 0);
    }

    /** Persentase bobot penjualan historis yang barangnya sedang ada stok. */
    private function persenTersedia()
    {
        $ada = 0;
        $prev = 0;
        foreach ($this->barangKode as $i => $key) {
            list($kd, , , $jumlah) = $this->barangJual[$key];
            if (($this->stok[$kd] ?? 0) >= $jumlah) {
                $ada += $this->barangKum[$i] - $prev;
            }
            $prev = $this->barangKum[$i];
        }
        return $this->barangTotal > 0 ? $ada / $this->barangTotal * 100 : 0;
    }

    /**
     * @return array ['nota' => [no_transaksi...], 'jam' => [kd_barang => detik kedatangan], 'nilai' => total]
     */
    private function simulasiPembelian($tgl, $batasDetik)
    {
        $hasil = ['nota' => [], 'jam' => [], 'nilai' => 0];
        $mode = self::MODE_BELI[$this->rencana[$tgl]['beli']];
        $mulai = 8 * 3600;
        $selesai = min(11 * 3600 + 1800, $batasDetik - 60);
        if ($selesai <= $mulai) {
            $mulai = 6 * 3600;
            if ($selesai <= $mulai) {
                return $hasil;
            }
        }

        // Kelompokkan barang yang stoknya di bawah ambang per supplier. Prioritas supplier = nilai penjualan
        // per hari yang terancam hilang (laju x harga x tingkat urgensi), supaya barang laris didahulukan.
        $perSupplier = [];
        $skor = [];
        $wajib = [];
        foreach ($this->infoBeli as $kd => $info) {
            $laju = $this->lajuEfektif($kd);
            if ($laju <= 0 || !isset($this->dijual[$kd])) {
                continue;
            }
            $cukup = ($this->stok[$kd] ?? 0) / $laju;
            if ($cukup < $mode['ambang']) {
                $perSupplier[$info[0]][$kd] = $cukup;
                $skor[$info[0]] = ($skor[$info[0]] ?? 0) + $laju * ($info[3] / $info[2]) * ($mode['ambang'] - $cukup);
                if ($cukup < self::REORDER_HARI) {
                    $wajib[$info[0]] = true;
                }
            }
        }
        foreach ($perSupplier as &$items) {
            asort($items);
        }
        unset($items);
        arsort($skor);

        $n = min(count($perSupplier), max(mt_rand($mode['nota'][0], $mode['nota'][1]), min(count($wajib), $mode['maks_wajib'])));
        if ($n === 0) {
            return $hasil;
        }
        $terpilih = [];
        foreach (array_slice(array_keys($skor), 0, $n) as $sup) {
            $terpilih[$sup] = $perSupplier[$sup];
        }

        $jam = [];
        for ($i = 0; $i < $n; $i++) {
            $jam[] = mt_rand($mulai, $selesai);
        }
        sort($jam);

        $i = 0;
        foreach ($terpilih as $sup => $items) {
            $detik = $jam[$i++];
            $no = $this->nomorBaru('t_pembelian', $tgl);
            $waktu = $this->waktu($tgl, $detik);
            $admin = self::USER_ADMIN[mt_rand(0, count(self::USER_ADMIN) - 1)];
            $this->rowsBeli[] = [
                $no, (string) $sup, self::KD_DIVISI, self::KD_JENIS, self::KD_KAS, '-', $waktu,
                date('Y-m-d\TH:i:s', strtotime($waktu . ' +14 days')), 1, 0, 0, 0, 0, 0, 0, '-', $admin,
                $this->waktu($tgl, $detik + mt_rand(10, 120)),
            ];
            foreach (array_slice($items, 0, self::MAX_ITEM_PER_NOTA_BELI, true) as $kd => $cukup) {
                $kd = (string) $kd; // key array numerik (barcode) otomatis jadi int di PHP
                list(, $satuan, $jumlah, $harga) = $this->infoBeli[$kd];
                $butuh = $this->lajuEfektif($kd) * mt_rand($mode['cover'][0], $mode['cover'][1]) - ($this->stok[$kd] ?? 0);
                $qty = max(1, (int) ceil($butuh / $jumlah));
                $this->rowsBeliDetail[] = [$no, $kd, $satuan, 1, $qty, $harga, 0, 0, 0, 0, 0, $qty * $harga];
                $hasil['nilai'] += $qty * $harga;
                $this->stok[$kd] = ($this->stok[$kd] ?? 0) + $qty * $jumlah;
                $this->hargaBeliUnit[$kd] = $harga / $jumlah;
                $hasil['jam'][$kd] = $detik;
            }
            $hasil['nota'][] = $no;
        }
        return $hasil;
    }

    private function simulasiPenjualan($tgl, $batasDetik, array $stokAwal, array $kedatangan, &$target, &$jumlahNota)
    {
        // Target omzet dari rencana harian (lihat rencanaHarian)
        $target = $this->baseOmzet * $this->rencana[$tgl]['faktor'];
        $fraksi = $this->fraksiJam($batasDetik);
        $target *= $fraksi;
        $jumlahNota = 0;
        if ($fraksi <= 0) {
            return 0;
        }

        $batas = $target * self::BATAS_ATAS_NOTA;
        $omzet = 0;
        $nota = [];
        $gagal = 0;
        while ($omzet < $target && $gagal < 100) {
            $lines = $this->buatNota($batas - $omzet);
            if (empty($lines)) {
                $gagal++;
                continue;
            }
            foreach ($lines as $l) {
                $omzet += $l[1] * $l[3];
            }
            $nota[] = ['lines' => $lines, 'detik' => $this->acakJam($batasDetik)];
        }

        // Nota yang butuh stok dari pembelian hari ini digeser ke setelah jam pembelian
        usort($nota, function ($a, $b) {
            return $a['detik'] <=> $b['detik'];
        });
        $terpakai = [];
        foreach ($nota as &$n) {
            $setelah = 0;
            foreach ($n['lines'] as $l) {
                $kd = $l[0];
                if (($terpakai[$kd] ?? 0) + $l[5] > ($stokAwal[$kd] ?? 0) + 1e-9
                    && isset($kedatangan['jam'][$kd]) && $n['detik'] <= $kedatangan['jam'][$kd]) {
                    $setelah = max($setelah, $kedatangan['jam'][$kd]);
                }
            }
            if ($setelah > 0) {
                $n['detik'] = min($batasDetik, mt_rand($setelah + 60, max($setelah + 60, min($batasDetik, 22 * 3600 + 3599))));
            }
            foreach ($n['lines'] as $l) {
                $terpakai[$l[0]] = ($terpakai[$l[0]] ?? 0) + $l[5];
            }
        }
        unset($n);
        usort($nota, function ($a, $b) {
            return $a['detik'] <=> $b['detik'];
        });

        foreach ($nota as $n) {
            $no = $this->nomorBaru('t_penjualan', $tgl);
            $waktu = $this->waktu($tgl, $n['detik']);
            $server = $this->waktu($tgl, $n['detik'] + mt_rand(1, 3));
            $this->rowsJual[] = [
                $no, $this->pilihBobot($this->customerBobot), self::KD_DIVISI, self::KD_JENIS, self::KD_KAS,
                self::KD_VOUCHER, '-', $waktu, $waktu, 1, 0, 0, 0, 0, 0, 0, '-',
                $n['detik'] < 15 * 3600 ? self::USER_KASIR_PAGI : self::USER_KASIR_SORE,
                $server, date('Y-m-d\TH:i:s', strtotime($server . ' -1 day')),
            ];
            foreach ($n['lines'] as $l) {
                list($kd, $harga, $satuan, $qty, $hbt) = $l;
                $this->rowsJualDetail[] = [
                    $no, $kd, $satuan, self::KD_PEGAWAI, 1, 0, 0, 0, 0, $harga, $qty, 0, 0, $harga * $qty, '-', round($hbt, 4),
                ];
            }
        }
        $jumlahNota = count($nota);
        return $omzet;
    }

    /**
     * Satu nota penjualan. Stok langsung dikurangi.
     * @return array of [kd_barang, harga_jual, kd_satuan, qty, harga_beli_terakhir]
     */
    private function buatNota($sisaNilai)
    {
        $k = $this->pilihBobot($this->itemPerNota);
        $lines = [];
        $nilai = 0;
        $dipakai = [];
        for ($i = 0; $i < $k; $i++) {
            $key = $this->pilihBarang($dipakai, $sisaNilai - $nilai);
            if ($key === null) {
                break;
            }
            list($kd, $satuan, $harga, $jumlah) = $this->barangJual[$key];
            $dipakai[$kd] = true;
            $sampel = $this->qtySampel[$key];
            $qty = $sampel[mt_rand(0, count($sampel) - 1)];
            $qty = min($qty, floor($this->stok[$kd] / $jumlah), floor(($sisaNilai - $nilai) / $harga));
            if ($qty < 1) {
                continue;
            }
            $this->stok[$kd] -= $qty * $jumlah;
            $this->terjualHariIni[$kd] = ($this->terjualHariIni[$kd] ?? 0) + $qty * $jumlah;
            $nilai += $harga * $qty;
            // qty di sini dalam satuan terkecil, untuk cek urutan jam vs pembelian
            $lines[] = [$kd, $harga, $satuan, $qty, $this->hargaBeliUnit[$kd] ?? 0, $qty * $jumlah];
        }
        return $lines;
    }

    /** Pilih barang+satuan sesuai bobot historis yang stoknya cukup dan harganya masih muat. */
    private function pilihBarang(array $kecuali, $sisaNilai)
    {
        for ($coba = 0; $coba < 40; $coba++) {
            $r = mt_rand(1, $this->barangTotal);
            $lo = 0;
            $hi = count($this->barangKum) - 1;
            while ($lo < $hi) {
                $mid = intdiv($lo + $hi, 2);
                if ($this->barangKum[$mid] < $r) {
                    $lo = $mid + 1;
                } else {
                    $hi = $mid;
                }
            }
            $key = $this->barangKode[$lo];
            list($kd, , $harga, $jumlah) = $this->barangJual[$key];
            if (!isset($kecuali[$kd]) && ($this->stok[$kd] ?? 0) >= $jumlah && $harga <= $sisaNilai) {
                return $key;
            }
        }
        return null;
    }

    private function simulasiBiaya($tgl, $batasDetik)
    {
        $daftar = [];
        if (!empty($this->biayaBobot) && $this->randf(0, 1) < $this->biayaPeluang) {
            $n = $this->pilihBobot($this->biayaPerHari ?: [1 => 1]);
            for ($i = 0; $i < $n; $i++) {
                $kd = $this->pilihBobot($this->biayaBobot);
                $s = $this->biayaSampel[$kd][mt_rand(0, count($this->biayaSampel[$kd]) - 1)];
                $daftar[] = [$kd, $s[0], $s[1]];
            }
        }
        if ((int) date('j', strtotime($tgl)) === self::HARI_LISTRIK && !empty($this->biayaSampel[self::KD_BIAYA_LISTRIK])) {
            $nom = array_sum(array_column($this->biayaSampel[self::KD_BIAYA_LISTRIK], 0)) / count($this->biayaSampel[self::KD_BIAYA_LISTRIK]);
            $daftar[] = [self::KD_BIAYA_LISTRIK, round($nom * $this->randf(0.9, 1.1), -2), 'LISTRIK'];
        }

        $mulai = 8 * 3600;
        $selesai = min(21 * 3600, $batasDetik - 60);
        if ($selesai <= $mulai || empty($daftar)) {
            return 0;
        }
        $jam = [];
        foreach ($daftar as $d) {
            $jam[] = mt_rand($mulai, $selesai);
        }
        sort($jam);
        foreach ($daftar as $i => $d) {
            $this->rowsBiaya[] = [
                $this->nomorBaru('t_biaya_operasional', $tgl), self::KD_DIVISI, $d[0], self::KD_JENIS, self::KD_KAS,
                $this->waktu($tgl, $jam[$i]), $d[1], '-', $d[2],
                self::USER_ADMIN[mt_rand(0, count(self::USER_ADMIN) - 1)], $this->waktu($tgl, $jam[$i] + mt_rand(5, 60)),
            ];
        }
        return count($daftar);
    }

    // =====================================================================================
    // Helper
    // =====================================================================================

    private function randf($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /** Acak distribusi normal standar (Box-Muller). */
    private function randn()
    {
        return sqrt(-2 * log(max(1e-12, $this->randf(0, 1)))) * cos(2 * M_PI * $this->randf(0, 1));
    }

    private function pilihBobot(array $bobot)
    {
        $r = $this->randf(0, array_sum($bobot));
        foreach ($bobot as $k => $w) {
            $r -= $w;
            if ($r <= 0) {
                return $k;
            }
        }
        return $k;
    }

    /** Porsi omzet harian yang sudah lewat s/d $batasDetik, berdasarkan pola jam. */
    private function fraksiJam($batasDetik)
    {
        $total = array_sum($this->jamBobot);
        $lewat = 0;
        foreach ($this->jamBobot as $h => $w) {
            $lewat += $w * max(0, min(1, ($batasDetik - $h * 3600) / 3600));
        }
        return $total > 0 ? $lewat / $total : 1;
    }

    private function acakJam($batasDetik)
    {
        for ($coba = 0; $coba < 100; $coba++) {
            $detik = $this->pilihBobot($this->jamBobot) * 3600 + mt_rand(0, 3599);
            if ($detik <= $batasDetik) {
                return $detik;
            }
        }
        return mt_rand(min(7 * 3600, $batasDetik), $batasDetik);
    }

    private function waktu($tgl, $detik)
    {
        return date('Y-m-d\TH:i:s', strtotime($tgl) + $detik);
    }

    private function nomorBaru($tabel, $tgl)
    {
        $ymd = date('ymd', strtotime($tgl));
        $seq = ($this->seqAwal[$tabel][$ymd] ?? 0) + 1;
        $this->seqAwal[$tabel][$ymd] = $seq;
        return self::PREFIX . $ymd . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    // =====================================================================================
    // Penulisan file SQL
    // =====================================================================================

    private function tulisSql($file, $from, $to, $seed)
    {
        $f = fopen($file, 'w');
        $sampai = date('Y-m-d', strtotime("$to +1 day"));
        $noDari = self::PREFIX . date('ymd', strtotime($from)) . '0000';
        $noSampai = self::PREFIX . date('ymd', strtotime($to)) . '9999';

        fwrite($f, "-- @periode: $from $to\n");
        fwrite($f, "-- Data dummy transaksi (penjualan, pembelian, biaya operasional) - lihat database/dummy/README.md\n");
        fwrite($f, '-- Dibuat ' . date('Y-m-d H:i:s') . ", seed $seed\n");
        fwrite($f, sprintf("-- Acuan omzet harian %s, %.0f nota/hari\n", number_format($this->baseOmzet, 0, ',', '.'), $this->baseNota));
        fwrite($f, sprintf("-- Total: penjualan %d (detail %d), pembelian %d (detail %d), biaya %d\n--\n",
            count($this->rowsJual), count($this->rowsJualDetail), count($this->rowsBeli), count($this->rowsBeliDetail), count($this->rowsBiaya)));
        fwrite($f, "-- bulan     hari  rata-rata omzet/hari  dev%   (aturan: rata-rata per bulan dalam ±10% acuan)\n");
        foreach ($this->ringkasanBulanan() as $b) {
            fwrite($f, sprintf("-- %s  %4d  %20s  %+5.1f\n", $b['bulan'], $b['hari'],
                number_format($b['rata_omzet'], 0, ',', '.'), $b['deviasi_pct']));
        }
        fwrite($f, "--\n-- tanggal     hari  nota  omzet          dev%   beli  nilai beli    jenis   biaya  keterangan\n");
        foreach ($this->ringkasan as $r) {
            fwrite($f, sprintf("-- %s  %s   %4d  %13s  %+6.1f  %4d  %12s  %-6s  %4d  %s\n", $r['tanggal'],
                substr(date('D', strtotime($r['tanggal'])), 0, 2), $r['nota_jual'],
                number_format($r['omzet'], 0, ',', '.'), $r['deviasi_pct'], $r['nota_beli'],
                number_format($r['nilai_beli'], 0, ',', '.'), $r['mode_beli'], $r['biaya'],
                trim($r['ket'] . ($r['parsial'] ? ' (s/d jam generate)' : ''))));
        }
        fwrite($f, "--\n-- Bisa dijalankan manual di SSMS / sqlcmd: copy seluruh isi file, lalu jalankan (Execute / F5).\n");
        fwrite($f, "-- File dipecah jadi batch kecil (pemisah GO) supaya hemat memori SQL Server, tetapi tetap SATU transaksi:\n");
        fwrite($f, "-- kalau satu batch gagal, semuanya di-rollback dan batch berikutnya otomatis berhenti.\n");
        fwrite($f, "-- SP turunan (harga beli, jurnal, PPh per bulan) jalan di akhir, hanya kalau insert berhasil.\n\n");

        // XACT_ABORT ON: error apa pun membatalkan transaksi. Batch berikutnya mengecek @@TRANCOUNT dan berhenti.
        $lanjut = "IF @@TRANCOUNT = 0 THROW 50010, 'Batch sebelumnya gagal, transaksi sudah di-rollback. Perbaiki lalu generate ulang.', 1;\n";
        fwrite($f, "SET NOCOUNT ON;\nSET XACT_ABORT ON;\nBEGIN TRANSACTION;\n\n");
        fwrite($f, "IF EXISTS (SELECT 1 FROM t_penjualan WHERE tanggal >= '$from' AND tanggal < '$sampai')\n");
        fwrite($f, "    THROW 50001, 'Sudah ada penjualan di periode $from s/d $to. Generate ulang dengan periode baru.', 1;\n");
        foreach (['t_penjualan', 't_pembelian', 't_biaya_operasional'] as $tabel) {
            fwrite($f, "IF EXISTS (SELECT 1 FROM $tabel WHERE no_transaksi IN (" . $this->nomorPertama($tabel) . "))\n");
            fwrite($f, "    THROW 50002, 'Nomor transaksi $tabel sudah terpakai. Generate ulang.', 1;\n");
        }
        fwrite($f, "GO\n\n");
        $this->guardBatch = $lanjut;

        $this->tulisInsert($f, 't_pembelian',
            'no_transaksi, kd_supplier, kd_divisi, kd_jenis, kd_kas, no_order, tanggal, tanggal_jatuh_tempo, status, diskon1, diskon2, diskon3, diskon4, pajak, ppnbm, keterangan, kd_user, tanggal_server',
            $this->rowsBeli);
        $this->tulisInsert($f, 't_pembelian_detail',
            'no_transaksi, kd_barang, kd_satuan, jenis, qty, harga_beli, diskon1, diskon2, diskon3, diskon4, point1, total',
            $this->rowsBeliDetail);
        $this->tulisInsert($f, 't_penjualan',
            'no_transaksi, kd_customer, kd_divisi, kd_jenis, kd_kas, kd_voucher, no_bukti, tanggal, tanggal_jatuh_tempo, status, diskon, diskon2, diskon3, diskon4, diskon_uang, pajak, keterangan, kd_user, tanggal_server, tanggal_setor',
            $this->rowsJual);
        $this->tulisInsert($f, 't_penjualan_detail',
            'no_transaksi, kd_barang, kd_satuan, kd_pegawai, jenis, diskon, diskon2, diskon3, diskon4, harga_jual, qty, point1, point2, total, keterangan, harga_beli_terakhir',
            $this->rowsJualDetail);
        $this->tulisInsert($f, 't_biaya_operasional',
            'no_transaksi, kd_divisi, kd_biaya, kd_jenis, kd_kas, tanggal, nominal, no_bukti, keterangan, kd_user, tanggal_server',
            $this->rowsBiaya);

        fwrite($f, $lanjut);
        fwrite($f, "IF EXISTS (SELECT 1 FROM v_stok_barang WHERE stok < 0)\n");
        fwrite($f, "    THROW 50003, 'Ada stok minus setelah insert (kemungkinan ada transaksi baru sejak generate). Dibatalkan, silakan generate ulang.', 1;\nGO\n\n");
        fwrite($f, $lanjut);
        fwrite($f, "COMMIT TRANSACTION;\nPRINT 'Insert dummy $from s/d $to selesai.';\nGO\n\n");

        // Hanya jalan kalau insert sudah ter-commit (nomor pertama ada di DB)
        $pertama = $this->rowsJual[0][0];
        fwrite($f, "-- @sp-mulai\n-- SP turunan (hanya jalan kalau insert di atas berhasil)\n");
        fwrite($f, "IF NOT EXISTS (SELECT 1 FROM t_penjualan WHERE no_transaksi = '$pertama')\n");
        fwrite($f, "    THROW 50011, 'Insert belum berhasil, SP turunan tidak dijalankan.', 1;\n");
        fwrite($f, implode("\n", self::perintahSp($from, $to)) . "\nPRINT 'SP turunan selesai.';\n-- @sp-selesai\nGO\n\n");

        // PPh bulan X bernomor HPJ<X+1>001 (lihat sp_GenerateHutangPPhFinal)
        $hpj = array_map(function ($bulan) {
            return "'HPJ" . date('Ym', strtotime("$bulan +1 month")) . "001'";
        }, self::bulanPeriode($from, $to));
        fwrite($f, "/* ROLLBACK MANUAL - hapus semua data dari file ini (termasuk PPh bulan terkait), lalu jalankan ulang SP harga beli & jurnal:\n");
        fwrite($f, "DELETE FROM t_penjualan_detail WHERE no_transaksi BETWEEN '$noDari' AND '$noSampai' AND LEN(no_transaksi) = 12;\n");
        fwrite($f, "DELETE FROM t_penjualan WHERE no_transaksi BETWEEN '$noDari' AND '$noSampai' AND LEN(no_transaksi) = 12;\n");
        fwrite($f, "DELETE FROM t_pembelian_detail WHERE no_transaksi BETWEEN '$noDari' AND '$noSampai' AND LEN(no_transaksi) = 12;\n");
        fwrite($f, "DELETE FROM t_pembelian WHERE no_transaksi BETWEEN '$noDari' AND '$noSampai' AND LEN(no_transaksi) = 12;\n");
        fwrite($f, "DELETE FROM t_biaya_operasional WHERE no_transaksi BETWEEN '$noDari' AND '$noSampai' AND LEN(no_transaksi) = 12;\n");
        fwrite($f, "DELETE FROM t_hutang_pajak WHERE no_transaksi IN (" . implode(',', $hpj) . ");\n");
        // PPh tidak di-generate ulang: bulan yang tersisa sebagian datanya akan terhitung parsial
        fwrite($f, implode("\n", array_slice(self::perintahSp($from, $to), 0, 2)) . "\n*/\n");
        fclose($f);
    }

    /** Rata-rata omzet harian per bulan (hari parsial tidak dihitung) vs acuan. */
    public function ringkasanBulanan()
    {
        $bulan = [];
        foreach ($this->ringkasan as $r) {
            if (!$r['parsial']) {
                $bulan[substr($r['tanggal'], 0, 7)][] = $r['omzet'];
            }
        }
        $hasil = [];
        foreach ($bulan as $b => $omzet) {
            $rata = array_sum($omzet) / count($omzet);
            $hasil[] = [
                'bulan' => $b,
                'hari' => count($omzet),
                'rata_omzet' => $rata,
                'deviasi_pct' => $this->baseOmzet > 0 ? ($rata / $this->baseOmzet - 1) * 100 : 0,
            ];
        }
        return $hasil;
    }

    /** Nomor pertama yang dipakai per hari (untuk guard bentrok nomor). */
    private function nomorPertama($tabel)
    {
        $rows = ['t_penjualan' => $this->rowsJual, 't_pembelian' => $this->rowsBeli, 't_biaya_operasional' => $this->rowsBiaya][$tabel];
        $pertama = [];
        foreach ($rows as $r) {
            $ymd = substr($r[0], 2, 6);
            if (!isset($pertama[$ymd])) {
                $pertama[$ymd] = "'" . $r[0] . "'";
            }
        }
        return empty($pertama) ? "''" : implode(',', $pertama);
    }

    private function tulisInsert($f, $tabel, $kolom, array $rows)
    {
        foreach (array_chunk($rows, self::BARIS_PER_BATCH) as $chunk) {
            fwrite($f, $this->guardBatch);
            fwrite($f, "INSERT INTO $tabel ($kolom) VALUES\n");
            $vals = [];
            foreach ($chunk as $r) {
                $vals[] = '(' . implode(',', array_map([$this, 'nilaiSql'], $r)) . ')';
            }
            fwrite($f, implode(",\n", $vals) . ";\nGO\n\n");
        }
    }

    private function nilaiSql($v)
    {
        if (is_int($v)) {
            return (string) $v;
        }
        if (is_float($v)) {
            return rtrim(rtrim(sprintf('%.4F', $v), '0'), '.');
        }
        return "'" . str_replace("'", "''", $v) . "'";
    }
}
