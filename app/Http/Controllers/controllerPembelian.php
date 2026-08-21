<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPembelian extends Controller
{
    public function viewPembelian()
    {
        $supplier = DB::select("SELECT
                                    kd_supplier,
                                    nama AS supplier
                                FROM m_supplier
                                ORDER BY nama");

        // Generate nomor transaksi format: BB + YYMMDD + 4-digit counter
        $no_transaksi_temporary = DB::select("SELECT top 1 no_transaksi FROM t_pembelian ORDER BY no_transaksi DESC");
        if (!empty($no_transaksi_temporary)) {
            $no_tr = substr($no_transaksi_temporary[0]->no_transaksi, -4);
            $incremented = str_pad((int)$no_tr + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $incremented = '0001';
        }
        $no_transaksi = 'BB' . date('ymd') . $incremented;

        // Tanggal pembelian terakhir untuk default filter From/To
        $last = DB::select("SELECT TOP 1 CONVERT(varchar(10), tanggal, 120) AS tanggal FROM t_pembelian ORDER BY tanggal DESC");
        $last_purchase_date = !empty($last) ? $last[0]->tanggal : date('Y-m-d');

        return view('Pembelian', [
            'supplier'           => $supplier,
            'no_transaksi'       => $no_transaksi,
            'last_purchase_date' => $last_purchase_date,
        ]);
    }

    public function getDataPembelian(Request $request)
    {
        $draw     = (int) $request->input('draw', 1);
        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 10);
        $search   = $request->input('search.value', '');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir         = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $columnsMap = [
            0 => 'ph.no_transaksi',
            1 => 'ph.tanggal',
            2 => 'ph.customer',
            3 => 'ph.jumlah_item',
            4 => 'ph.total_diskon',
            5 => 'ph.total',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'ph.tanggal';

        if ($length <= 0) {
            $length = 10;
        }

        $where = [];
        $bindings = [];

        if (!empty($dateFrom)) {
            $where[] = "CAST(ph.tanggal AS DATE) >= ?";
            $bindings[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where[] = "CAST(ph.tanggal AS DATE) <= ?";
            $bindings[] = $dateTo;
        }

        $bindingsFiltered = $bindings;
        if (!empty($search)) {
            $where[] = "(ph.no_transaksi LIKE ? OR ph.customer LIKE ?)";
            $bindingsFiltered[] = "%$search%";
            $bindingsFiltered[] = "%$search%";
        }

        $whereSqlFiltered = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal = DB::select("SELECT COUNT(*) AS c FROM pembelian_header")[0]->c;

        $recordsFiltered = DB::select("
            SELECT COUNT(*) AS c
            FROM pembelian_header ph
            $whereSqlFiltered
        ", $bindingsFiltered)[0]->c;

        $sql = "SELECT
                    ph.no_transaksi,
                    ph.tanggal,
                    ph.customer,
                    ph.jumlah_item,
                    ph.total_diskon,
                    ph.total
                FROM pembelian_header ph
                $whereSqlFiltered
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";

        $data = DB::select($sql, $bindingsFiltered);

        // Pastikan desimal dibatasi 2 angka di backend
        foreach ($data as $row) {
            $row->total_diskon = (float)round((float)$row->total_diskon, 2);
            $row->total = (float)round((float)$row->total, 2);
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function getBarangSatuanBeli(Request $request)
    {
        $keyword = $request->q ?? '';
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $bindings = ["%$keyword%", "%$keyword%"];

        $total = DB::select("SELECT COUNT(*) AS c
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE (m_barang.nama LIKE ? OR m_barang.kd_barang LIKE ?)", $bindings)[0]->c;

        // Query simplified: hapus correlated subquery harga_beli (lambat).
        // Harga beli ditampilkan "-" di FE, user lihat di row yang dipilih dari t_pembelian_detail.
        // Atau nanti fetch via AJAX terpisah kalau perlu.
        $dataBarangSatuan = DB::select("SELECT
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual,
                                0 AS harga_beli
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE (m_barang.nama LIKE ? OR m_barang.kd_barang LIKE ?)
                            ORDER BY m_barang.nama ASC
                            OFFSET ? ROWS FETCH NEXT ? ROWS ONLY",
                            array_merge($bindings, [$offset, $perPage]));

        return response()->json([
            'dataBarangSatuan' => $dataBarangSatuan,
            'total'            => (int) $total,
            'page'             => $page,
            'perPage'          => $perPage,
        ]);
    }

    // Lookup barang berdasarkan barcode (kd_barang) secara exact match.
    // Dipakai oleh input scan barcode: 1 barcode bisa mengembalikan
    // beberapa baris kalau barang punya lebih dari satu satuan.
    public function getBarangByBarcode(Request $request)
    {
        $barcode = trim($request->barcode);

        $dataBarangSatuan = DB::select("SELECT
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual,
                                ISNULL((
                                    SELECT TOP 1 d.harga_beli
                                    FROM t_pembelian_detail d
                                    INNER JOIN t_pembelian p ON d.no_transaksi = p.no_transaksi
                                    WHERE d.kd_barang = m_barang_satuan.kd_barang
                                      AND d.kd_satuan = m_barang_satuan.kd_satuan
                                    ORDER BY p.tanggal DESC, p.no_transaksi DESC
                                ), 0) AS harga_beli
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE m_barang.kd_barang = ?
                            ORDER BY m_barang_satuan.jumlah", [$barcode]);

        return response()->json(['dataBarangSatuan' => $dataBarangSatuan]);
    }

    public function inputPembelian(Request $request)
    {
        $no_transaksi        = $request->no_transaksi;
        $kd_supplier         = $request->kd_supplier;
        $no_order            = $request->no_order ?: '-';
        $tanggal             = $request->tanggal ?: date('Y-m-d');
        $tanggal_jatuh_tempo = $request->tanggal_jatuh_tempo;
        $masterDiskon        = (float) ($request->masterDiskon ?? 0);
        $pajak               = (float) ($request->pajak ?? 0);
        $ppnbm               = (float) ($request->ppnbm ?? 0);
        $keterangan          = $request->keterangan ?: '-';
        $kd_user             = session('user.kd_user') ?? 'UAA000';

        try {
            DB::transaction(function () use ($no_transaksi, $kd_supplier, $no_order, $tanggal, $tanggal_jatuh_tempo, $masterDiskon, $pajak, $ppnbm, $keterangan, $kd_user, $request) {
                DB::insert("INSERT INTO t_pembelian
                            (no_transaksi, kd_supplier, kd_divisi, kd_jenis, kd_kas, no_order,
                             tanggal, tanggal_jatuh_tempo, status,
                             diskon1, diskon2, diskon3, diskon4, pajak, ppnbm,
                             keterangan, kd_user, tanggal_server)
                            VALUES
                            (?, ?, 'DAA000', 'JAA000', 'KAA001', ?,
                             ?, ?, 1,
                             ?, 0, 0, 0, ?, ?,
                             ?, ?, GETDATE())",
                            [$no_transaksi, $kd_supplier, $no_order, $tanggal, $tanggal_jatuh_tempo,
                             $masterDiskon, $pajak, $ppnbm, $keterangan, $kd_user]);

                $products = $request->products ?? [];
                foreach ($products as $product) {
                    $kd_barang  = $product['kd_barang'];
                    $kd_satuan  = $product['kd_satuan'];
                    $qty        = (float) $product['qty'];
                    $harga_beli = (float) $product['harga_beli'];
                    $diskon_dt  = (float) ($product['diskon_dt'] ?? 0);
                    $total      = ($qty * $harga_beli) - ($diskon_dt * $qty);

                    DB::insert("INSERT INTO t_pembelian_detail
                                (no_transaksi, kd_barang, kd_satuan, jenis, qty, harga_beli,
                                 diskon1, diskon2, diskon3, diskon4, point1, total)
                                VALUES
                                (?, ?, ?, 1, ?, ?, ?, 0, 0, 0, 0, ?)",
                                [$no_transaksi, $kd_barang, $kd_satuan, $qty, $harga_beli,
                                 $diskon_dt, $total]);
                }
            });
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan pembelian.'], 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan pembelian');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pembelian berhasil disimpan.']);
        }
        return redirect()->route('index.pembelian');
    }

    public function getDetailPembelian(Request $request)
    {
        $keyword = $request->no_transaksi;

        $sql = DB::select("SELECT
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                ROUND(t_pembelian_detail.harga_beli, 2) AS harga_beli,
                                ROUND(t_pembelian_detail.qty, 2) AS qty,
                                ROUND(IIF(t_pembelian_detail.diskon1 >= 1, t_pembelian_detail.diskon1, (t_pembelian_detail.diskon1 * t_pembelian_detail.harga_beli)), 2) AS diskon,
                                ROUND(t_pembelian_detail.total, 2) AS total
                            FROM t_pembelian
                            INNER JOIN t_pembelian_detail ON t_pembelian.no_transaksi = t_pembelian_detail.no_transaksi
                            INNER JOIN m_barang ON t_pembelian_detail.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON t_pembelian_detail.kd_satuan = m_satuan.kd_satuan
                            WHERE t_pembelian.no_transaksi = ?", [$keyword]);
        return response()->json(['dataDetail' => $sql]);
    }

    public function editPembelian(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $kd_barang    = $request->kd_barang;
        $kd_satuan    = $request->kd_satuan;
        $qty          = (float) $request->qty;
        $diskon       = (float) $request->diskon;

        // Ambil harga_beli untuk hitung ulang total
        $row = DB::select("SELECT harga_beli FROM t_pembelian_detail
                           WHERE no_transaksi = ? AND kd_barang = ? AND kd_satuan = ?",
                           [$no_transaksi, $kd_barang, $kd_satuan]);
        $harga_beli = !empty($row) ? (float) $row[0]->harga_beli : 0;
        $total = ($qty * $harga_beli) - ($diskon * $qty);

        DB::update("UPDATE t_pembelian_detail
                    SET qty = ?, diskon1 = ?, total = ?
                    WHERE no_transaksi = ? AND kd_barang = ? AND kd_satuan = ?",
                    [$qty, $diskon, $total, $no_transaksi, $kd_barang, $kd_satuan]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Detail pembelian berhasil diperbarui.']);
        }
        return redirect()->route('index.pembelian');
    }

    public function hapusPembelian(Request $request)
    {
        $no_transaksi = $request->no_transaksi;

        if (empty($no_transaksi)) {
            return response()->json(['success' => false, 'message' => 'No. transaksi tidak valid.'], 422);
        }

        try {
            DB::transaction(function () use ($no_transaksi) {
                DB::delete("DELETE FROM t_pembelian_detail WHERE no_transaksi = ?", [$no_transaksi]);
                DB::delete("DELETE FROM t_pembelian WHERE no_transaksi = ?", [$no_transaksi]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus transaksi.'], 500);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaksi pembelian berhasil dihapus.']);
        }
        return redirect()->route('index.pembelian');
    }
}
