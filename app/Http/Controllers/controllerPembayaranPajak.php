<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPembayaranPajak extends Controller
{
    /**
     * Generate kode NTPN dengan format: PPH + YYMMDD + 3 digit increment.
     * Contoh: PPH260519001 (tanggal 19 Mei 2026, urutan ke-1 pada hari itu).
     * 3 digit terakhir reset/increment sesuai tanggal berjalan (hari ini).
     */
    private function generateNtpn(): string
    {
        $prefix = 'PPH' . date('ymd'); // PPH + YYMMDD

        $row = DB::select(
            "SELECT TOP 1 ntpn FROM t_pembayaran_pajak
             WHERE ntpn LIKE ? ORDER BY ntpn DESC",
            [$prefix . '%']
        );

        if (!empty($row)) {
            $lastSeq = (int) substr($row[0]->ntpn, -3);
            $seq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $seq = '001';
        }

        return $prefix . $seq;
    }

    public function viewPembayaranPajak()
    {
        $ntpn = $this->generateNtpn();

        return view('PembayaranPajak', ['ntpn' => $ntpn]);
    }

    /** Nama bulan Indonesia untuk format tampilan periode. */
    private const NAMA_BULAN = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];

    /** Format "YYYY-MM" menjadi "NamaBulan YYYY". Kosong -> "-". */
    private function formatPeriode(?string $periodeIso): string
    {
        if (empty($periodeIso) || !preg_match('/^(\d{4})-(\d{2})/', $periodeIso, $m)) {
            return '-';
        }
        $namaBln = self::NAMA_BULAN[$m[2]] ?? $m[2];
        return $namaBln . ' ' . $m[1];
    }

    /**
     * Server-side DataTables endpoint untuk tabel Pembayaran Pajak.
     */
    public function getDataPembayaranPajak(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        // Whitelist ORDER BY. Index 2 (PERIODE) & 6 (aksi) tidak dapat diurutkan
        // (tidak ada kolom fisik) -> fallback ke id.
        $columnsMap = [
            0 => 'tanggal',
            1 => 'masa_pajak',
            3 => 'jenis_pajak',
            4 => 'nominal',
            5 => 'ntpn',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'id';
        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(masa_pajak LIKE ? OR jenis_pajak LIKE ? OR ntpn LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM t_pembayaran_pajak")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM t_pembayaran_pajak $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    id,
                    CONVERT(varchar(10), tanggal, 23)  AS tanggal_iso,     -- YYYY-MM-DD (input date modal edit)
                    CONVERT(varchar(10), tanggal, 105) AS tanggal_tampil,  -- DD-MM-YYYY (kolom tampil)
                    masa_pajak,                                            -- YYYY-MM (dipakai tampil & input month edit)
                    jenis_pajak,
                    nominal,
                    ntpn,
                    CONVERT(varchar(7), periode, 23)   AS periode_iso,     -- YYYY-MM (input periode modal edit)
                    reff_no
                FROM t_pembayaran_pajak
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        foreach ($data as $row) {
            $row->periode_iso    = $row->periode_iso ?? '';
            $row->periode_tampil = $this->formatPeriode($row->periode_iso);
            $row->reff_no        = $row->reff_no ?? '';
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function inputPembayaranPajak(Request $request)
    {
        $tanggal     = $request->tanggal;
        $masa_pajak  = $request->masa_pajak;
        $jenis_pajak = $request->jenis_pajak;
        $nominal     = (float) ($request->nominal ?? 0);
        $periode     = $request->periode ?: null;               // YYYY-MM-01 atau null
        $reff_no     = $request->reff_no ?: null;               // no_transaksi hutang pajak
        // NTPN di-generate ulang di server agar tidak duplikat walau form basi.
        $ntpn        = $this->generateNtpn();

        DB::insert("INSERT INTO t_pembayaran_pajak
                    (tanggal, masa_pajak, jenis_pajak, nominal, ntpn, periode, reff_no)
                    VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$tanggal, $masa_pajak, $jenis_pajak, $nominal, $ntpn, $periode, $reff_no]);

        // Tandai hutang pajak terkait sebagai sudah dibayar (status_pajak = 2).
        if (!empty($reff_no)) {
            DB::update("UPDATE t_hutang_pajak SET status_pajak = 2 WHERE no_transaksi = ?", [$reff_no]);
        }

        return redirect()->route('index.pembayaran.pajak');
    }

    public function editPembayaranPajak(Request $request)
    {
        $id          = $request->edit_id;
        $tanggal     = $request->edit_tanggal;
        $masa_pajak  = $request->edit_masa_pajak;
        $jenis_pajak = $request->edit_jenis_pajak;
        $nominal     = (float) ($request->edit_nominal ?? 0);
        $periode     = $request->edit_periode ?: null;          // YYYY-MM-01 atau null

        // NTPN & reff_no tidak diubah saat edit (kode auto / referensi hutang).
        DB::update("UPDATE t_pembayaran_pajak
                    SET tanggal = ?, masa_pajak = ?, jenis_pajak = ?, nominal = ?, periode = ?
                    WHERE id = ?",
                    [$tanggal, $masa_pajak, $jenis_pajak, $nominal, $periode, $id]);

        return redirect()->route('index.pembayaran.pajak');
    }

    public function hapusPembayaranPajak(Request $request)
    {
        $id = $request->hapus_id;
        DB::delete("DELETE FROM t_pembayaran_pajak WHERE id = ?", [$id]);
        return redirect()->route('index.pembayaran.pajak');
    }

    /**
     * Generate hutang pajak PPh Final secara manual via stored procedure
     * sp_GenerateHutangPPhFinal. Idempotent (SP melewati periode yang sudah ada).
     * Mengembalikan jumlah hutang pajak baru yang terbentuk.
     */
    public function generateHutangPajak()
    {
        try {
            $before = DB::select("SELECT COUNT(*) AS c FROM t_hutang_pajak")[0]->c;
            DB::statement("EXEC sp_GenerateHutangPPhFinal");
            $after  = DB::select("SELECT COUNT(*) AS c FROM t_hutang_pajak")[0]->c;

            return response()->json([
                'success' => true,
                'added'   => (int) $after - (int) $before,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Server-side DataTables endpoint untuk daftar HUTANG PAJAK.
     *
     * Mode ditentukan oleh query "paid":
     *   paid=0 (default) -> hutang pajak yang BELUM dibayar
     *   paid=1           -> hutang pajak yang SUDAH dibayar
     *
     * "Sudah dibayar" = ada baris pembayaran (t_pembayaran_pajak) yang
     * mereferensikan no_transaksi hutang tsb via kolom reff_no.
     */
    public function getDataHutangPajak(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');
        $paid   = $request->query('paid', '0') === '1';

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        // Whitelist ORDER BY berbeda tergantung mode (kolom tampil berbeda).
        $columnsMap = $paid
            ? [0 => 'h.no_transaksi', 1 => 'h.tgl_pajak', 2 => 'h.jenis_pajak', 3 => 'h.nominal', 4 => 'p.ntpn', 5 => 'p.tanggal']
            : [0 => 'h.no_transaksi', 1 => 'h.tgl_pajak', 2 => 'h.jatuh_tempo', 3 => 'h.jenis_pajak', 4 => 'h.nominal'];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'h.tgl_pajak';
        if ($length <= 0) { $length = 10; }

        // Kondisi paid/unpaid berdasarkan keberadaan pembayaran (reff_no).
        $paidCond = $paid
            ? "EXISTS (SELECT 1 FROM t_pembayaran_pajak pp WHERE pp.reff_no = h.no_transaksi)"
            : "NOT EXISTS (SELECT 1 FROM t_pembayaran_pajak pp WHERE pp.reff_no = h.no_transaksi)";

        $where    = [$paidCond];
        $bindings = [];
        if (!empty($search)) {
            $where[]    = "(h.no_transaksi LIKE ? OR h.jenis_pajak LIKE ? OR h.keterangan LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $recordsTotal = DB::select(
            "SELECT COUNT(*) AS c FROM t_hutang_pajak h WHERE $paidCond"
        )[0]->c;
        $recordsFiltered = DB::select(
            "SELECT COUNT(*) AS c FROM t_hutang_pajak h $whereSql", $bindings
        )[0]->c;

        // Untuk mode "sudah dibayar" ambil juga detail pembayaran terkait
        // (1 pembayaran terbaru per hutang) via OUTER APPLY.
        $sql = "SELECT
                    h.no_transaksi,
                    CONVERT(varchar(10), h.tgl_pajak, 105)   AS tgl_pajak_tampil,
                    CONVERT(varchar(7),  h.tgl_pajak, 23)    AS periode_iso,     -- YYYY-MM (prefill)
                    CONVERT(varchar(10), h.jatuh_tempo, 105) AS jatuh_tempo_tampil,
                    h.jenis_pajak,
                    h.nominal,
                    h.keterangan,
                    p.ntpn                                    AS ntpn,
                    CONVERT(varchar(10), p.tanggal, 105)     AS tanggal_bayar
                FROM t_hutang_pajak h
                OUTER APPLY (
                    SELECT TOP 1 ntpn, tanggal
                    FROM t_pembayaran_pajak pp
                    WHERE pp.reff_no = h.no_transaksi
                    ORDER BY pp.id DESC
                ) p
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * API: ambil nominal hutang pajak dari t_hutang_pajak berdasarkan
     * periode (year+month yang dipilih di front end) dan jenis pajak.
     *
     * Filter pada kolom tgl_pajak memakai rentang [awal bulan, awal bulan berikutnya)
     * agar tetap akurat walau tgl_pajak bertipe DATETIME.
     * Jika ada >1 baris pada periode tsb, ambil 1 baris terbaru (tgl_pajak DESC).
     *
     * Request: periode = "YYYY-MM-01", jenis_pajak = string
     * Response JSON: { found: bool, nominal: float }
     */
    public function cekHutangPajak(Request $request)
    {
        $periode     = (string) $request->query('periode', '');
        $jenis_pajak = (string) $request->query('jenis_pajak', '');

        // Validasi format periode wajib YYYY-MM-01.
        if (!preg_match('/^\d{4}-\d{2}-01$/', $periode)) {
            return response()->json(['found' => false, 'nominal' => 0]);
        }

        $row = DB::select(
            "SELECT TOP 1 nominal
             FROM t_hutang_pajak
             WHERE tgl_pajak >= ?
               AND tgl_pajak < DATEADD(MONTH, 1, ?)
               AND jenis_pajak = ?
             ORDER BY tgl_pajak DESC",
            [$periode, $periode, $jenis_pajak]
        );

        if (empty($row)) {
            return response()->json(['found' => false, 'nominal' => 0]);
        }

        return response()->json([
            'found'   => true,
            'nominal' => (float) $row[0]->nominal,
        ]);
    }
}
