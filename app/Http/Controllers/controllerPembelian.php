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
            0 => 't_pembelian.no_transaksi',
            1 => 't_pembelian.tanggal',
            2 => 't_pembelian.tanggal_jatuh_tempo',
            3 => 'm_supplier.nama',
            4 => 't_pembelian.diskon1',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 't_pembelian.tanggal';

        if ($length <= 0) {
            $length = 10;
        }

        $where = [];
        $bindings = [];

        if (!empty($dateFrom)) {
            $where[] = "CAST(t_pembelian.tanggal AS DATE) >= ?";
            $bindings[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where[] = "CAST(t_pembelian.tanggal AS DATE) <= ?";
            $bindings[] = $dateTo;
        }

        $bindingsFiltered = $bindings;
        if (!empty($search)) {
            $where[] = "(t_pembelian.no_transaksi LIKE ? OR m_supplier.nama LIKE ?)";
            $bindingsFiltered[] = "%$search%";
            $bindingsFiltered[] = "%$search%";
        }

        $whereSqlFiltered = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal = DB::select("SELECT COUNT(*) AS c FROM t_pembelian")[0]->c;

        $recordsFiltered = DB::select("
            SELECT COUNT(*) AS c
            FROM t_pembelian
            INNER JOIN m_supplier ON t_pembelian.kd_supplier = m_supplier.kd_supplier
            $whereSqlFiltered
        ", $bindingsFiltered)[0]->c;

        $sql = "SELECT
                    t_pembelian.no_transaksi,
                    CONCAT(DAY(t_pembelian.tanggal), ' ',
                        CASE MONTH(t_pembelian.tanggal)
                            WHEN 1 THEN 'Januari'
                            WHEN 2 THEN 'Februari'
                            WHEN 3 THEN 'Maret'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'Mei'
                            WHEN 6 THEN 'Juni'
                            WHEN 7 THEN 'Juli'
                            WHEN 8 THEN 'Agustus'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'Oktober'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'Desember'
                        END, ' ', YEAR(t_pembelian.tanggal)
                    ) AS tanggal_pembelian,
                    CONVERT(varchar(10), t_pembelian.tanggal_jatuh_tempo, 120) AS tanggal_jatuh_tempo,
                    m_supplier.nama AS supplier,
                    t_pembelian.diskon1 AS diskon
                FROM t_pembelian
                INNER JOIN m_supplier ON t_pembelian.kd_supplier = m_supplier.kd_supplier
                $whereSqlFiltered
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";

        $data = DB::select($sql, $bindingsFiltered);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function getBarangSatuanBeli(Request $request)
    {
        $keyword = $request->q;

        // Kolom harga_beli tidak ada di m_barang_satuan. Ambil harga beli terakhir
        // dari transaksi pembelian sebelumnya (per barang + satuan) sebagai nilai
        // default; tetap bisa diubah manual saat input pembelian.
        $dataBarangSatuan = DB::select("SELECT TOP 10
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
                            WHERE (m_barang.nama LIKE ? OR m_barang.kd_barang LIKE ?)
                            ORDER BY m_barang.nama", ["%$keyword%", "%$keyword%"]);

        return response()->json(['dataBarangSatuan' => $dataBarangSatuan]);
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
        $tanggal_jatuh_tempo = $request->tanggal_jatuh_tempo;
        $masterDiskon        = (float) ($request->masterDiskon ?? 0);
        $pajak               = (float) ($request->pajak ?? 0);
        $ppnbm               = (float) ($request->ppnbm ?? 0);
        $keterangan          = $request->keterangan ?: '-';
        $kd_user             = session('user.kd_user') ?? 'UAA000';

        DB::insert("INSERT INTO t_pembelian
                    (no_transaksi, kd_supplier, kd_divisi, kd_jenis, kd_kas, no_order,
                     tanggal, tanggal_jatuh_tempo, status,
                     diskon1, diskon2, diskon3, diskon4, pajak, ppnbm,
                     keterangan, kd_user, tanggal_server)
                    VALUES
                    (?, ?, 'DAA000', 'JAA000', 'KAA001', ?,
                     GETDATE(), ?, 1,
                     ?, 0, 0, 0, ?, ?,
                     ?, ?, GETDATE())",
                    [$no_transaksi, $kd_supplier, $no_order, $tanggal_jatuh_tempo,
                     $masterDiskon, $pajak, $ppnbm, $keterangan, $kd_user]);

        $products = $request->products ?? [];
        foreach ($products as $product) {
            $kd_barang  = $product['kd_barang'];
            $kd_satuan  = $product['kd_satuan'];
            $qty        = (float) $product['qty'];
            $harga_beli = (float) $product['harga_beli'];
            $diskon_dt  = (float) ($product['diskon_dt'] ?? 0);
            $total      = ($qty * $harga_beli) - ($diskon_dt * $qty);

            // Kolom "nomor" adalah IDENTITY (auto-increment), jadi tidak di-insert manual.
            DB::insert("INSERT INTO t_pembelian_detail
                        (no_transaksi, kd_barang, kd_satuan, jenis, qty, harga_beli,
                         diskon1, diskon2, diskon3, diskon4, point1, total)
                        VALUES
                        (?, ?, ?, 1, ?, ?, ?, 0, 0, 0, 0, ?)",
                        [$no_transaksi, $kd_barang, $kd_satuan, $qty, $harga_beli,
                         $diskon_dt, $total]);
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
                                t_pembelian_detail.harga_beli,
                                t_pembelian_detail.qty,
                                t_pembelian_detail.diskon1 AS diskon,
                                t_pembelian_detail.total
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
