<?php

namespace App\Http\Controllers;

use App\Support\ExcelXml;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class LaporanController extends Controller
{
    public function index()
    {
        # code...
    }
    // Tidak dipakai: view r_laba_rugi_harian tidak ada di skripsi_pos.
    // Halaman aktif: viewLaporanLabaRugi() -> fn_Laporan_LabaRugi.
    // public function getLaporanLabaRugi(Request $req)
    // {
    //     $awal = $req->awal;
    //     $akhir = $req->akhir;
    //     $data = DB::select("SELECT * from r_laba_rugi_harian WHERE tanggal BETWEEN '2025-04-03' AND '2025-04-08'");
    //     // $data = DB::select("CALL GetLabaRugi('$awal','$akhir')");
    //     // return response()->json(['data' => $data]);
    //     return view('LaporanLabaRugi', ['data'=>$data]);
    // }

    // =====================================================================
    // Helper: tanggal default (tanggal jurnal terakhir)
    // =====================================================================
    private function defaultTgl()
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        return $latest[0]->tgl ?? date('Y-m-d');
    }

    private function resolvePeriode(Request $req)
    {
        $latest = $this->defaultTgl();
        return [
            $req->tgl_awal  ?? $latest,
            $req->tgl_akhir ?? $latest,
        ];
    }

    // =====================================================================
    // Query masing-masing laporan (dipakai ulang oleh view & export)
    // =====================================================================

    private function dataJurnalUmum($tgl_awal, $tgl_akhir)
    {
        $data = DB::select("
            SELECT j.tgl_jurnal, j.no_bukti, j.coa_kode, c.coa_nama, j.debit, j.kredit, j.sumber
            FROM jurnal_umum j
            LEFT JOIN m_coa c ON c.coa_kode = j.coa_kode
            WHERE j.tgl_jurnal BETWEEN ? AND ?
            ORDER BY j.tgl_jurnal ASC, j.no_bukti ASC
        ", [$tgl_awal, $tgl_akhir]);

        $total_debit = 0;
        $total_kredit = 0;
        foreach ($data as $row) {
            $total_debit  += (float) $row->debit;
            $total_kredit += (float) $row->kredit;
        }

        return [
            'data'         => $data,
            'total_debit'  => $total_debit,
            'total_kredit' => $total_kredit,
        ];
    }

    private function dataLabaRugi($tgl_awal, $tgl_akhir)
    {
        $rows = DB::select("SELECT * FROM dbo.fn_Laporan_LabaRugi(?, ?) ORDER BY coa_kode", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $total_pendapatan = 0;
        $total_beban = 0;
        foreach ($rows as $r) {
            $kategori = $r->kategori;
            if (!isset($grouped[$kategori])) {
                $grouped[$kategori] = ['items' => [], 'subtotal' => 0];
            }
            $grouped[$kategori]['items'][] = $r;
            $grouped[$kategori]['subtotal'] += (float) $r->nilai;

            if (stripos($kategori, 'pendapatan') !== false) {
                $total_pendapatan += (float) $r->nilai;
            } else {
                $total_beban += (float) $r->nilai;
            }
        }
        $laba_bersih = $total_pendapatan - $total_beban;

        return [
            'grouped'          => $grouped,
            'total_pendapatan' => $total_pendapatan,
            'total_beban'      => $total_beban,
            'laba_bersih'      => $laba_bersih,
        ];
    }

    private function dataNeraca($tgl_awal, $tgl_akhir)
    {/*  */
        $rows = DB::select("SELECT * FROM dbo.fn_Neraca_Saldo(?, ?) ORDER BY coa_kode", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $grand_debit = 0;
        $grand_kredit = 0;
        $grand_saldo = 0;
        foreach ($rows as $r) {
            $tipe = $r->coa_tipe ?? '-';
            if (!isset($grouped[$tipe])) {
                $grouped[$tipe] = ['items' => [], 'debit' => 0, 'kredit' => 0, 'saldo' => 0];
            }
            $grouped[$tipe]['items'][]   = $r;
            $grouped[$tipe]['debit']    += (float) $r->total_debit;
            $grouped[$tipe]['kredit']   += (float) $r->total_kredit;
            $grouped[$tipe]['saldo']    += (float) $r->saldo;
            $grand_debit  += (float) $r->total_debit;
            $grand_kredit += (float) $r->total_kredit;
            $grand_saldo  += (float) $r->saldo;
        }

        return [
            'grouped'      => $grouped,
            'grand_debit'  => $grand_debit,
            'grand_kredit' => $grand_kredit,
            'grand_saldo'  => $grand_saldo,
        ];
    }

    // =====================================================================
    // Halaman (view)
    // =====================================================================

    public function viewJurnalUmum(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataJurnalUmum($tgl_awal, $tgl_akhir);

        return view('JurnalUmum', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanLabaRugi(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataLabaRugi($tgl_awal, $tgl_akhir);

        return view('LaporanLabaRugiPage', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanNeraca(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataNeraca($tgl_awal, $tgl_akhir);

        return view('LaporanNeraca', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanArusKas(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);

        $rows = DB::select("EXEC sp_Arus_Kas ?, ?", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $grand_total = 0;
        foreach ($rows as $r) {
            $parts = explode(' - ', $r->aktivitas, 2);
            $group = trim($parts[0]);
            $label = isset($parts[1]) ? trim($parts[1]) : $r->aktivitas;

            if (!isset($grouped[$group])) {
                $grouped[$group] = ['items' => [], 'subtotal' => 0];
            }
            $grouped[$group]['items'][]   = (object) ['label' => $label, 'total' => $r->total];
            $grouped[$group]['subtotal'] += (float) $r->total;
            $grand_total += (float) $r->total;
        }

        return view('LaporanArusKas', [
            'grouped'     => $grouped,
            'grand_total' => $grand_total,
            'tgl_awal'    => $tgl_awal,
            'tgl_akhir'   => $tgl_akhir,
        ]);
    }

    // =====================================================================
    // Laporan Stok (server-side DataTables dari view v_stok_barang)
    // =====================================================================

    public function viewLaporanStok(Request $req)
    {
        // Rincian persediaan s/d tanggal; saldo pembanding = akun 103 di neraca pada tanggal yang sama.
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataNeraca($tgl_awal, $tgl_akhir);

        $persediaan = collect($d['grouped']['ASSET']['items'] ?? [])
            ->first(function ($it) { return trim($it->coa_kode) === '103'; });

        return view('LaporanStok', [
            'tgl_akhir' => $tgl_akhir,
            'saldo'     => $persediaan ? (float) $persediaan->saldo : 0,
        ]);
    }

    public function getDataLaporanStok(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_barang',
            1 => 'nama',
            2 => 'stok',
            3 => 'satuan_terkecil',
            4 => 'harga_beli',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'nama';

        if ($length <= 0) {
            $length = 10;
        }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_barang LIKE ? OR nama LIKE ? OR satuan_terkecil LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $t0 = microtime(true);
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM v_stok_barang")[0]->c;
        $t1 = microtime(true);
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM v_stok_barang $whereSql", $bindings)[0]->c;
        $t2 = microtime(true);

        $sql = "SELECT kd_barang, nama, stok, satuan_terkecil, ROUND(harga_beli, 2) AS harga_beli
                FROM v_stok_barang
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";

        $data = DB::select($sql, $bindings);
        $t3 = microtime(true);

        \Illuminate\Support\Facades\Log::info('LaporanStok timing', [
            'count_total_s'    => round($t1 - $t0, 3),
            'count_filtered_s' => round($t2 - $t1, 3),
            'select_data_s'    => round($t3 - $t2, 3),
        ]);

        foreach ($data as $row) {
            $row->harga_beli = (float) round((float) $row->harga_beli, 2);
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    // =====================================================================
    // Rincian persediaan per barang (modal di Laporan Neraca), server-side
    // =====================================================================

    public function getDataPersediaanNeraca(Request $request)
    {
        $tgl = (string) $request->input('tgl_akhir', '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return response()->json(['error' => 'tgl_akhir tidak valid'], 422);
        }

        $draw   = (int) $request->input('draw', 1);
        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length < 1 ? 10 : min($length, 100);
        $search = trim((string) $request->input('search.value', ''));

        $columnsMap = [
            0 => 'kd_barang',
            1 => 'nama',
            2 => 'satuan_terkecil',
            3 => 'qty_beli',
            4 => 'nilai_beli',
            5 => 'qty_jual',
            6 => 'nilai_hpp',
            7 => 'qty_sisa',
            8 => 'nilai_persediaan',
        ];
        $orderColumn = $columnsMap[(int) $request->input('order.0.column', 8)] ?? 'nilai_persediaan';
        $orderDir    = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderSql    = $orderColumn === 'kd_barang' ? "kd_barang $orderDir" : "$orderColumn $orderDir, kd_barang ASC";

        $where = '';
        $bindings = [$tgl];
        if ($search !== '') {
            // escape wildcard LIKE milik SQL Server agar dicari sebagai teks biasa
            $like = '%' . str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $search) . '%';
            $where = 'WHERE (nama LIKE ? OR kd_barang LIKE ?)';
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $sumCols = ['qty_beli', 'nilai_beli', 'qty_jual', 'nilai_hpp', 'qty_sisa', 'nilai_persediaan'];
        $windowSums = implode(",
                   ", array_map(function ($c) {
            return "SUM($c) OVER() AS f_$c";
        }, $sumCols));

        // COUNT/SUM OVER() membawa jumlah & total hasil filter di query yang sama dengan halaman data
        $rows = DB::select("
            SELECT kd_barang, nama, satuan_terkecil, " . implode(', ', $sumCols) . ",
                   COUNT(*) OVER() AS f_count,
                   $windowSums
            FROM dbo.fn_Persediaan_Barang(?)
            $where
            ORDER BY $orderSql
            OFFSET $start ROWS FETCH NEXT $length ROWS ONLY
        ", $bindings);

        if (!empty($rows)) {
            $filtered = ['count' => (int) $rows[0]->f_count];
            foreach ($sumCols as $c) {
                $filtered[$c] = round((float) $rows[0]->{"f_$c"}, 2);
            }
        } else {
            // halaman kosong (mis. tidak ada hasil pencarian): hitung terpisah
            $filtered = $this->sumPersediaan($tgl, $where, $bindings, $sumCols);
        }

        $total = $search === '' ? $filtered : $this->sumPersediaan($tgl, '', [$tgl], $sumCols);

        foreach ($rows as $row) {
            foreach ($sumCols as $c) {
                $row->$c = round((float) $row->$c, 2);
                unset($row->{"f_$c"});
            }
            unset($row->f_count);
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total['count'],
            'recordsFiltered' => $filtered['count'],
            'data'            => $rows,
            'total'           => $total,      // semua barang
            'filtered'        => $filtered,   // hasil pencarian (sama dengan total kalau tidak mencari)
            'total_nilai'     => $total['nilai_persediaan'],
            'filtered_nilai'  => $filtered['nilai_persediaan'],
        ]);
    }

    // Rincian persediaan untuk halaman Laporan Stok (kolom lebih lengkap dari modal neraca)
    public function getDataPersediaanStok(Request $request)
    {
        $tgl = (string) $request->input('tgl_akhir', '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return response()->json(['error' => 'tgl_akhir tidak valid'], 422);
        }

        $draw   = (int) $request->input('draw', 1);
        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length < 1 ? 10 : min($length, 100);
        $search = trim((string) $request->input('search.value', ''));

        $columnsMap = [
            0  => 'kd_barang',
            1  => 'nama',
            2  => 'satuan_terkecil',
            3  => 'harga_beli_terakhir',
            4  => 'harga_jual',
            5  => 'qty_beli',
            6  => 'harga_beli',
            7  => 'nilai_beli',
            8  => 'qty_jual',
            9  => 'harga_jual_avg',
            10 => 'nilai_hpp',
            11 => 'qty_sisa',
            12 => 'nilai_persediaan',
        ];
        $orderColumn = $columnsMap[(int) $request->input('order.0.column', 12)] ?? 'nilai_persediaan';
        $orderDir    = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderSql    = $orderColumn === 'kd_barang' ? "kd_barang $orderDir" : "$orderColumn $orderDir, kd_barang ASC";

        $where = '';
        $bindings = [$tgl];
        if ($search !== '') {
            // escape wildcard LIKE milik SQL Server agar dicari sebagai teks biasa
            $like = '%' . str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $search) . '%';
            $where = 'WHERE (nama LIKE ? OR kd_barang LIKE ?)';
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $sumCols = ['qty_beli', 'nilai_beli', 'qty_jual', 'nilai_hpp', 'qty_sisa', 'nilai_persediaan'];
        $windowSums = implode(",
                   ", array_map(function ($c) {
            return "SUM($c) OVER() AS f_$c";
        }, $sumCols));

        // COUNT/SUM OVER() membawa jumlah & total hasil filter di query yang sama dengan halaman data
        $rows = DB::select("
            SELECT kd_barang, nama, satuan_terkecil, " . implode(', ', $sumCols) . ",
                   CASE WHEN qty_beli = 0 THEN 0 ELSE nilai_beli / qty_beli END AS harga_beli,
                   CASE WHEN qty_jual = 0 THEN 0 ELSE nilai_hpp / qty_jual END AS harga_jual_avg,
                   harga_beli_terakhir, harga_jual,
                   COUNT(*) OVER() AS f_count,
                   $windowSums
            FROM (
                SELECT p.*, ISNULL(v.harga_beli, 0) AS harga_beli_terakhir,
                       ISNULL(j.harga_jual, 0) AS harga_jual
                FROM dbo.fn_Persediaan_Barang(?) p
                OUTER APPLY (SELECT TOP 1 harga_beli FROM v_stok_barang WHERE kd_barang = p.kd_barang) v
                OUTER APPLY (
                    SELECT TOP 1 bs.harga_jual FROM m_barang_satuan bs
                    JOIN m_satuan s ON s.kd_satuan = bs.kd_satuan
                    WHERE bs.kd_barang = p.kd_barang AND s.nama = p.satuan_terkecil
                ) j
            ) x
            $where
            ORDER BY $orderSql
            OFFSET $start ROWS FETCH NEXT $length ROWS ONLY
        ", $bindings);

        if (!empty($rows)) {
            $filtered = ['count' => (int) $rows[0]->f_count];
            foreach ($sumCols as $c) {
                $filtered[$c] = round((float) $rows[0]->{"f_$c"}, 2);
            }
        } else {
            // halaman kosong (mis. tidak ada hasil pencarian): hitung terpisah
            $filtered = $this->sumPersediaan($tgl, $where, $bindings, $sumCols);
        }

        $total = $search === '' ? $filtered : $this->sumPersediaan($tgl, '', [$tgl], $sumCols);

        foreach ($rows as $row) {
            foreach ($sumCols as $c) {
                $row->$c = round((float) $row->$c, 2);
                unset($row->{"f_$c"});
            }
            $row->harga_beli = round((float) $row->harga_beli, 2);
            $row->harga_beli_terakhir = round((float) $row->harga_beli_terakhir, 2);
            $row->harga_jual = round((float) $row->harga_jual, 2);
            $row->harga_jual_avg = round((float) $row->harga_jual_avg, 2);
            unset($row->f_count);
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total['count'],
            'recordsFiltered' => $filtered['count'],
            'data'            => $rows,
            'total'           => $total,      // semua barang
            'filtered'        => $filtered,   // hasil pencarian (sama dengan total kalau tidak mencari)
            'total_nilai'     => $total['nilai_persediaan'],
            'filtered_nilai'  => $filtered['nilai_persediaan'],
        ]);
    }

    private function sumPersediaan($tgl, $where, array $bindings, array $sumCols)
    {
        $selects = implode(', ', array_map(function ($c) {
            return "ISNULL(SUM($c), 0) AS $c";
        }, $sumCols));

        $r = DB::select("
            SELECT COUNT(*) AS c, $selects
            FROM dbo.fn_Persediaan_Barang(?)
            $where
        ", $bindings)[0];

        $out = ['count' => (int) $r->c];
        foreach ($sumCols as $c) {
            $out[$c] = round((float) $r->$c, 2);
        }
        return $out;
    }

    // =====================================================================
    // Rincian Kas / buku besar akun 101 (modal di Laporan Neraca), server-side
    // =====================================================================

    public function getDataKasNeraca(Request $request)
    {
        $tgl = (string) $request->input('tgl_akhir', '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return response()->json(['error' => 'tgl_akhir tidak valid'], 422);
        }

        $draw   = (int) $request->input('draw', 1);
        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length < 1 ? 10 : min($length, 100);
        $search = trim((string) $request->input('search.value', ''));

        // kolom tanggal diurutkan lewat "urut" supaya urutan dalam satu hari tetap sama dengan saldo berjalan
        $columnsMap = [
            0 => 'urut',
            1 => 'no_bukti',
            2 => 'sumber',
            3 => 'debit',
            4 => 'kredit',
            5 => 'saldo_berjalan',
        ];
        $orderColumn = $columnsMap[(int) $request->input('order.0.column', 0)] ?? 'urut';
        $orderDir    = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderSql    = $orderColumn === 'urut' ? "urut $orderDir" : "$orderColumn $orderDir, urut ASC";

        $where = '';
        $bindings = [$tgl];
        if ($search !== '') {
            // escape wildcard LIKE milik SQL Server agar dicari sebagai teks biasa
            $like = '%' . str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $search) . '%';
            $where = 'WHERE (no_bukti LIKE ? OR sumber LIKE ?)';
            array_push($bindings, $like, $like);
        }

        // filter dipasang di luar function: saldo_berjalan tetap saldo kas sebenarnya walau hasil difilter
        $rows = DB::select("
            SELECT urut, tgl_jurnal, no_bukti, sumber, debit, kredit, saldo_berjalan,
                   COUNT(*) OVER() AS f_count,
                   SUM(debit) OVER() AS f_debit,
                   SUM(kredit) OVER() AS f_kredit
            FROM dbo.fn_Kas_Detail(?)
            $where
            ORDER BY $orderSql
            OFFSET $start ROWS FETCH NEXT $length ROWS ONLY
        ", $bindings);

        if (!empty($rows)) {
            $filtered = [
                'count'  => (int) $rows[0]->f_count,
                'debit'  => round((float) $rows[0]->f_debit, 2),
                'kredit' => round((float) $rows[0]->f_kredit, 2),
            ];
        } else {
            // halaman kosong (mis. tidak ada hasil pencarian): hitung terpisah
            $filtered = $this->sumKas($where, $bindings);
        }
        $filtered['saldo'] = round($filtered['debit'] - $filtered['kredit'], 2);

        if ($search === '') {
            $total = $filtered;
        } else {
            $total = $this->sumKas('', [$tgl]);
            $total['saldo'] = round($total['debit'] - $total['kredit'], 2);
        }

        foreach ($rows as $row) {
            unset($row->f_count, $row->f_debit, $row->f_kredit);
            foreach (['debit', 'kredit', 'saldo_berjalan'] as $k) {
                $row->$k = round((float) $row->$k, 2);
            }
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total['count'],
            'recordsFiltered' => $filtered['count'],
            'data'            => $rows,
            'total'           => $total,      // semua transaksi kas
            'filtered'        => $filtered,   // hasil pencarian (sama dengan total kalau tidak mencari)
        ]);
    }

    private function sumKas($where, array $bindings)
    {
        $r = DB::select("
            SELECT COUNT(*) AS c, ISNULL(SUM(debit), 0) AS d, ISNULL(SUM(kredit), 0) AS k
            FROM dbo.fn_Kas_Detail(?)
            $where
        ", $bindings)[0];

        return [
            'count'  => (int) $r->c,
            'debit'  => round((float) $r->d, 2),
            'kredit' => round((float) $r->k, 2),
        ];
    }

    // =====================================================================
    // Rincian Laba Rugi (modal di Laporan Laba Rugi), server-side
    // =====================================================================

    // Pendapatan Penjualan (401) per barang: dbo.fn_LabaRugi_PerBarang, tampilkan kolom nilai_penjualan
    public function getDataLabaRugiPenjualan(Request $request)
    {
        return $this->labaRugiPerBarang($request, 'nilai_penjualan');
    }

    // Harga Pokok Penjualan (503) per barang: function yang sama, tampilkan kolom nilai_hpp
    public function getDataLabaRugiHpp(Request $request)
    {
        return $this->labaRugiPerBarang($request, 'nilai_hpp');
    }

    private function labaRugiPerBarang(Request $request, $nilaiCol)
    {
        $from = 'dbo.fn_LabaRugi_PerBarang(?, ?)';
        [$awal, $akhir] = [(string) $request->input('tgl_awal', ''), (string) $request->input('tgl_akhir', '')];
        if (!$this->isTanggal($awal) || !$this->isTanggal($akhir)) {
            return response()->json(['error' => 'tanggal tidak valid'], 422);
        }

        return response()->json($this->pageDetail(
            $request,
            $from,
            [$awal, $akhir],
            [0 => 'kd_barang', 1 => 'nama', 2 => 'satuan_terkecil', 3 => 'qty_jual', 4 => $nilaiCol],
            'kd_barang',
            ['nama', 'kd_barang'],
            ['qty_jual', $nilaiCol],
            $nilaiCol
        ));
    }

    // Pendapatan Lain (402), Beban Operasional (501), Beban Pajak Final (502) per transaksi -> dbo.fn_LabaRugi_Transaksi
    public function getDataLabaRugiTransaksi(Request $request)
    {
        [$awal, $akhir] = [(string) $request->input('tgl_awal', ''), (string) $request->input('tgl_akhir', '')];
        $coa = (string) $request->input('coa', '');
        if (!$this->isTanggal($awal) || !$this->isTanggal($akhir) || !in_array($coa, ['402', '501', '502'], true)) {
            return response()->json(['error' => 'parameter tidak valid'], 422);
        }

        return response()->json($this->pageDetail(
            $request,
            'dbo.fn_LabaRugi_Transaksi(?, ?, ?)',
            [$awal, $akhir, $coa],
            [0 => 'urut', 1 => 'no_bukti', 2 => 'keterangan', 3 => 'nilai'],
            'urut',
            ['no_bukti', 'keterangan'],
            ['nilai'],
            'urut',
            ['tgl_jurnal']
        ));
    }

    private function isTanggal($s)
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $s);
    }

    /**
     * Pagination server-side untuk table-valued function (format DataTables).
     * Filter dipasang di luar function; total & jumlah hasil filter dihitung dengan window function
     * di query yang sama dengan halaman data.
     *
     * @param string $from         FROM clause (nama function + placeholder ?)
     * @param array  $fromBindings binding untuk placeholder di $from
     * @param array  $columnsMap   indeks kolom DataTables => nama kolom (white-list untuk ORDER BY)
     * @param string $tieBreak     kolom pengurut kedua agar urutan halaman stabil
     * @param array  $searchCols   kolom yang dicari (LIKE)
     * @param array  $sumCols      kolom angka yang dijumlahkan (total semua & total hasil filter)
     * @param string $defaultCol   urutan awal (menurun) kalau DataTables tidak mengirim order
     * @param array  $extraCols    kolom non-angka tambahan yang ikut di-SELECT
     */
    private function pageDetail(Request $request, $from, array $fromBindings, array $columnsMap, $tieBreak,
                                array $searchCols, array $sumCols, $defaultCol, array $extraCols = [])
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length < 1 ? 10 : min($length, 100);
        $search = trim((string) $request->input('search.value', ''));

        if ($request->has('order.0.column')) {
            $orderColumn = $columnsMap[(int) $request->input('order.0.column')] ?? $defaultCol;
            $orderDir    = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        } else {
            $orderColumn = $defaultCol;
            $orderDir    = 'DESC';
        }
        $orderSql = $orderColumn === $tieBreak ? "$orderColumn $orderDir" : "$orderColumn $orderDir, $tieBreak ASC";

        $where = '';
        $bindings = $fromBindings;
        if ($search !== '') {
            // escape wildcard LIKE milik SQL Server agar dicari sebagai teks biasa
            $like = '%' . str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], $search) . '%';
            $where = 'WHERE (' . implode(' OR ', array_map(function ($c) { return "$c LIKE ?"; }, $searchCols)) . ')';
            foreach ($searchCols as $c) {
                $bindings[] = $like;
            }
        }

        $selectCols = implode(', ', array_merge(array_values($columnsMap), $extraCols));
        $windowSums = implode(', ', array_map(function ($c) { return "SUM($c) OVER() AS f_$c"; }, $sumCols));

        $rows = DB::select("
            SELECT $selectCols, COUNT(*) OVER() AS f_count, $windowSums
            FROM $from
            $where
            ORDER BY $orderSql
            OFFSET $start ROWS FETCH NEXT $length ROWS ONLY
        ", $bindings);

        $sumSql = function ($whereSql) use ($from, $sumCols) {
            return "SELECT COUNT(*) AS c, " . implode(', ', array_map(function ($c) { return "ISNULL(SUM($c), 0) AS $c"; }, $sumCols))
                 . " FROM $from $whereSql";
        };
        $toTotals = function ($r, $prefix, $countKey) use ($sumCols) {
            $t = ['count' => (int) $r->$countKey];
            foreach ($sumCols as $c) {
                $t[$c] = round((float) $r->{$prefix . $c}, 2);
            }
            return $t;
        };

        $filtered = !empty($rows)
            ? $toTotals($rows[0], 'f_', 'f_count')
            : $toTotals(DB::select($sumSql($where), $bindings)[0], '', 'c');   // halaman kosong: hitung terpisah
        $total = $search === ''
            ? $filtered
            : $toTotals(DB::select($sumSql(''), $fromBindings)[0], '', 'c');

        foreach ($rows as $row) {
            foreach ($sumCols as $c) {
                $row->$c = round((float) $row->$c, 2);
                unset($row->{"f_$c"});
            }
            unset($row->f_count);
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $total['count'],
            'recordsFiltered' => $filtered['count'],
            'data'            => $rows,
            'total'           => $total,      // semua data
            'filtered'        => $filtered,   // hasil pencarian (sama dengan total kalau tidak mencari)
        ];
    }

    // =====================================================================
    // Export Excel (multi-laporan dalam 1 file, 1 sheet per laporan)
    // =====================================================================

    public function export(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);

        // Laporan yang dipilih (checkbox). Default: jurnal_umum bila kosong.
        $reports = (array) $req->input('reports', []);
        $reports = array_values(array_filter($reports, function ($r) {
            return in_array($r, ['jurnal_umum', 'laba_rugi', 'neraca'], true);
        }));
        if (empty($reports)) {
            $reports = ['jurnal_umum'];
        }

        $xls = new ExcelXml();

        foreach ($reports as $r) {
            if ($r === 'jurnal_umum') {
                $xls->addSheet('Jurnal Umum', $this->sheetJurnalUmum($tgl_awal, $tgl_akhir));
            } elseif ($r === 'laba_rugi') {
                $xls->addSheet('Laba Rugi', $this->sheetLabaRugi($tgl_awal, $tgl_akhir));
            } elseif ($r === 'neraca') {
                $xls->addSheet('Neraca', $this->sheetNeraca($tgl_awal, $tgl_akhir));
            }
        }

        $filename = 'Laporan_' . str_replace('-', '', $tgl_awal) . '_' . str_replace('-', '', $tgl_akhir) . '.xls';

        return $xls->download($filename);
    }

    // =====================================================================
    // Generate / Refresh Jurnal Umum
    // Memanggil SP sp_Generate_Jurnal_Umum dari 2 hari lalu s/d sekarang.
    // =====================================================================
    public function refreshJurnal(Request $req)
    {
        $start = date('Y-m-d H:i:s', strtotime('-2 days'));
        $end   = date('Y-m-d H:i:s');

        try {
            DB::statement("EXEC sp_Generate_Jurnal_Umum ?, ?", [$start, $end]);
            return back()->with('flash', [
                'type' => 'success',
                'text' => "Jurnal umum berhasil di-generate untuk periode $start s/d $end.",
            ]);
        } catch (\Throwable $e) {
            return back()->with('flash', [
                'type' => 'error',
                'text' => 'Gagal generate jurnal: ' . $e->getMessage(),
            ]);
        }
    }

    private function periodeLabel($tgl_awal, $tgl_akhir)
    {
        $fmt = function ($t) {
            $ts = strtotime($t);
            return $ts ? date('d/m/Y', $ts) : $t;
        };
        return 'Periode: ' . $fmt($tgl_awal) . ' s/d ' . $fmt($tgl_akhir);
    }

    private function sheetJurnalUmum($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataJurnalUmum($tgl_awal, $tgl_akhir);
        $rows = [];

        $rows[] = ExcelXml::row([ExcelXml::text('JURNAL UMUM', 'title', 7)], 22);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 7)];
        $rows[] = []; // baris kosong

        $rows[] = ExcelXml::row([
            ExcelXml::text('NO', 'header'),
            ExcelXml::text('TANGGAL', 'header'),
            ExcelXml::text('NO. BUKTI', 'header'),
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('NAMA COA', 'header'),
            ExcelXml::text('SUMBER', 'header'),
            ExcelXml::text('DEBIT', 'header'),
            ExcelXml::text('KREDIT', 'header'),
        ], 30);

        $no = 0;
        foreach ($d['data'] as $row) {
            $no++;
            $ts = strtotime($row->tgl_jurnal);
            $rows[] = [
                ExcelXml::text($no, 'cellCenter'),
                ExcelXml::text($ts ? date('d/m/Y', $ts) : $row->tgl_jurnal, 'cellCenter'),
                ExcelXml::text($row->no_bukti, 'cellCenter'),
                ExcelXml::text($row->coa_kode, 'cellCenter'),
                ExcelXml::text($row->coa_nama, 'cell'),
                ExcelXml::text($row->sumber, 'cell'),
                ExcelXml::number($row->debit, 'money'),
                ExcelXml::number($row->kredit, 'money'),
            ];
        }

        if ($no === 0) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 7)];
        }

        // 'TOTAL' di-merge menutup kolom NO..SUMBER (A:F), lalu nilai di DEBIT (G) & KREDIT (H).
        $rows[] = [
            ExcelXml::text('TOTAL', 'totalLabelDark', 5),
            ExcelXml::number($d['total_debit'], 'moneyTotal'),
            ExcelXml::number($d['total_kredit'], 'moneyTotal'),
        ];

        return $rows;
    }

    private function sheetLabaRugi($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataLabaRugi($tgl_awal, $tgl_akhir);
        $rows = [];

        $rows[] = ExcelXml::row([ExcelXml::text('LAPORAN LABA RUGI', 'title', 2)], 22);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 2)];
        $rows[] = [];

        $rows[] = ExcelXml::row([
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('NAMA AKUN', 'header'),
            ExcelXml::text('NILAI', 'header'),
        ], 30);

        if (empty($d['grouped'])) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 2)];
        } else {
            foreach ($d['grouped'] as $kategori => $group) {
                $rows[] = [ExcelXml::text(strtoupper($kategori), 'group', 2)];
                foreach ($group['items'] as $item) {
                    $rows[] = [
                        ExcelXml::text($item->coa_kode, 'cellCenter'),
                        ExcelXml::text($item->coa_nama, 'cell'),
                        ExcelXml::number($item->nilai, 'money'),
                    ];
                }
                $rows[] = ExcelXml::row([
                    ExcelXml::text('Subtotal ' . $kategori, 'totalLabel', 1),
                    ExcelXml::number($group['subtotal'], 'moneyBold'),
                ], 32);
            }
        }

        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL PENDAPATAN', 'totalLabel', 1),
            ExcelXml::number($d['total_pendapatan'], 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL BEBAN', 'totalLabel', 1),
            ExcelXml::number($d['total_beban'], 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text($d['laba_bersih'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH', 'totalLabelDark', 1),
            ExcelXml::number($d['laba_bersih'], 'moneyTotal'),
        ], 32);

        return $rows;
    }

    private function sheetNeraca($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataNeraca($tgl_awal, $tgl_akhir);
        $grouped = $d['grouped'];
        $rows = [];

        $tipeLabels = [
            'ASSET'     => 'ASET',
            'LIABILITY' => 'LIABILITAS (KEWAJIBAN)',
            'EQUITY'    => 'EKUITAS (MODAL)',
        ];

        $totalAset       = $grouped['ASSET']['saldo']     ?? 0;
        $totalLiabilitas = $grouped['LIABILITY']['saldo'] ?? 0;
        $totalEkuitas    = $grouped['EQUITY']['saldo']    ?? 0;
        $totalPasiva     = $totalLiabilitas + $totalEkuitas;
        $isBalance       = abs($totalAset - $totalPasiva) < 0.01;

        $rows[] = ExcelXml::row([ExcelXml::text('LAPORAN NERACA (BALANCE SHEET)', 'title', 2)], 40);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 2)];
        $rows[] = [];

        $rows[] = ExcelXml::row([
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('NAMA AKUN', 'header'),
            ExcelXml::text('JUMLAH', 'header'),
        ], 30);

        $adaData = false;
        $blok = function ($key, $label, $total) use (&$rows, $grouped) {
            if (empty($grouped[$key])) {
                return false;
            }
            $rows[] = [ExcelXml::text($label, 'group', 2)];
            foreach ($grouped[$key]['items'] as $item) {
                $rows[] = [
                    ExcelXml::text($item->coa_kode, 'cellCenter'),
                    ExcelXml::text($item->coa_nama, 'cell'),
                    ExcelXml::number($item->saldo, 'money'),
                ];
            }
            $rows[] = ExcelXml::row([
                ExcelXml::text('TOTAL ' . $label, 'totalLabel', 1),
                ExcelXml::number($total, 'moneyBold'),
            ], 32);
            return true;
        };

        if ($blok('ASSET', $tipeLabels['ASSET'], $totalAset)) {
            $adaData = true;
        }
        if ($blok('LIABILITY', $tipeLabels['LIABILITY'], $totalLiabilitas)) {
            $adaData = true;
        }
        if ($blok('EQUITY', $tipeLabels['EQUITY'], $totalEkuitas)) {
            $adaData = true;
        }

        if (!$adaData) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 2)];
        }

        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL LIABILITAS + EKUITAS', 'totalLabel', 1),
            ExcelXml::number($totalPasiva, 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text($isBalance ? 'BALANCE SHEET (Seimbang)' : 'BALANCE SHEET (Tidak Seimbang)', 'totalLabelDark', 1),
            ExcelXml::number($totalAset, 'moneyTotal'),
        ], 32);

        return $rows;
    }
}
