<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPenjualan extends Controller
{
    public function viewPenjualan(){
        $customer = DB::select("SELECT
                                    kd_customer,
                                    nama AS customer
                                FROM m_customer
                                ORDER BY nama");
        $no_transaksi_temporary = DB::select("SELECT top 1 no_transaksi FROM t_penjualan ORDER BY no_transaksi DESC");
        $no_tr = substr($no_transaksi_temporary[0]->no_transaksi, -4);
        $incremented = str_pad((int)$no_tr + 1, 4, '0', STR_PAD_LEFT);
        $no_transaksi = 'PJ' . date('Ymd') . $incremented;

        // Tanggal penjualan terakhir untuk default filter From/To
        $last = DB::select("SELECT TOP 1 CONVERT(varchar(10), tanggal, 120) AS tanggal FROM t_penjualan ORDER BY tanggal DESC");
        $last_sale_date = !empty($last) ? $last[0]->tanggal : date('Y-m-d');

        return view('Penjualan', [
            'customer' => $customer,
            'no_transaksi' => $no_transaksi,
            'last_sale_date' => $last_sale_date,
        ]);
    }

    public function getDataPenjualan(Request $request)
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
            0 => 't_penjualan.no_transaksi',
            1 => 't_penjualan.tanggal',
            2 => 't_penjualan.diskon',
            3 => 'm_customer.nama',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 't_penjualan.tanggal';

        if ($length <= 0) {
            $length = 10;
        }

        $where = [];
        $bindings = [];

        if (!empty($dateFrom)) {
            $where[] = "CAST(t_penjualan.tanggal AS DATE) >= ?";
            $bindings[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where[] = "CAST(t_penjualan.tanggal AS DATE) <= ?";
            $bindings[] = $dateTo;
        }

        $bindingsFiltered = $bindings;
        if (!empty($search)) {
            $where[] = "(t_penjualan.no_transaksi LIKE ? OR m_customer.nama LIKE ?)";
            $bindingsFiltered[] = "%$search%";
            $bindingsFiltered[] = "%$search%";
        }

        $whereSqlFiltered = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total semua data (tanpa filter sama sekali)
        $recordsTotal = DB::select("SELECT COUNT(*) AS c FROM t_penjualan")[0]->c;

        // Total setelah filter tanggal + search
        $recordsFiltered = DB::select("
            SELECT COUNT(*) AS c
            FROM t_penjualan
            INNER JOIN m_customer ON t_penjualan.kd_customer = m_customer.kd_customer
            $whereSqlFiltered
        ", $bindingsFiltered)[0]->c;

        // Data halaman
        $sql = "SELECT
                    t_penjualan.no_transaksi,
                    CONCAT(DAY(t_penjualan.tanggal), ' ',
                        CASE MONTH(t_penjualan.tanggal)
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
                        END, ' ', YEAR(t_penjualan.tanggal)
                    ) AS tanggal_penjualan,
                    t_penjualan.diskon,
                    m_customer.nama AS customer
                FROM t_penjualan
                INNER JOIN m_customer ON t_penjualan.kd_customer = m_customer.kd_customer
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

    public function getBarangSatuan(Request $request){
        $keyword = $request->q; // assuming you're passing ?q=keyword

        $dataBarangSatuan = DB::select("SELECT TOP 10
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE (m_barang.nama LIKE ? OR m_barang.kd_barang LIKE ?)
                            ORDER BY m_barang.nama", ["%$keyword%", "%$keyword%"]);
        return response()->json(['dataBarangSatuan'=>$dataBarangSatuan]);
    }

    // Lookup barang berdasarkan barcode (kd_barang) exact match untuk input scan.
    // 1 barcode bisa mengembalikan beberapa baris kalau barang punya banyak satuan.
    public function getBarangByBarcode(Request $request){
        $barcode = trim($request->barcode);

        $dataBarangSatuan = DB::select("SELECT
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE m_barang.kd_barang = ?
                            ORDER BY m_barang_satuan.jumlah", [$barcode]);
        return response()->json(['dataBarangSatuan'=>$dataBarangSatuan]);
    }

    public function inputPenjualan(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $kd_customer = $request->kd_customer;
        $kd_pegawai = $request->kd_pegawai;
        $masterDiskon = $request->masterDiskon;


        DB::insert("INSERT INTO t_penjualan
        (no_transaksi, kd_customer, kd_divisi,kd_jenis, kd_kas, tanggal, diskon, keterangan, status,kd_voucher,no_bukti,tanggal_jatuh_tempo)
        VALUES
        (?, ?, 1, '-', '-', GETDATE(), ?, '-', 1, 'KAA000', '-', DATEADD(DAY, 7, GETDATE()))
        ", [$no_transaksi, $kd_customer, $masterDiskon]);

        $products = $request->products;

        // Ambil harga beli terakhir untuk semua barang yang dipilih sekaligus
        // lewat table function dbo.getHargaBeliTerakhir('kd1,kd2,...').
        $kdBarangList = collect($products)->pluck('kd_barang')->filter()->unique()->implode(',');
        $hargaBeliMap = [];
        if ($kdBarangList !== '') {
            $hargaBeliRows = DB::select("SELECT kd_barang, harga_beli FROM dbo.getHargaBeliTerakhir(?)", [$kdBarangList]);
            foreach ($hargaBeliRows as $row) {
                $hargaBeliMap[$row->kd_barang] = $row->harga_beli;
            }
        }

        foreach ($products as $product) {
            $kd_barang = $product['kd_barang'];
            $kd_satuan = $product['kd_satuan'];
            $qty = $product['qty'];
            $diskon_dt = $product['diskon_dt'];
            $harga_jual = $product['harga_jual'];
            $harga_beli_terakhir = $hargaBeliMap[$kd_barang] ?? 0;

            DB::insert("INSERT INTO t_penjualan_detail
                    (no_transaksi, kd_barang, kd_satuan,kd_pegawai, jenis, harga_jual,qty, diskon, keterangan, harga_beli_terakhir)
                    VALUES
                    (?, ?, ?, ?, '1', ?, ?, ?, '-', ?)",
                    [$no_transaksi, $kd_barang, $kd_satuan, $kd_pegawai, $harga_jual, $qty, $diskon_dt, $harga_beli_terakhir]);
        }
        return redirect()->route('index.penjualan');
    }


    public function getDetailPenjualan(Request $request)
    {
        $keyword = $request->no_transaksi;

        $sql = DB::select("SELECT
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                harga_jual,
                                qty,
                                t_penjualan_detail.diskon AS diskon
                            FROM t_penjualan 
                            INNER JOIN t_penjualan_detail ON t_penjualan.no_transaksi = t_penjualan_detail.no_transaksi
                            INNER JOIN m_barang ON t_penjualan_detail.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON t_penjualan_detail.kd_satuan = m_satuan.kd_satuan
                            WHERE t_penjualan.no_transaksi=?", ["$keyword"]);
        return response()->json(['dataDetail'=>$sql]);
    }

    public function editPenjualan(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $kd_barang = $request->kd_barang;
        $kd_satuan = $request->kd_satuan;
        $qty = $request->qty;
        $diskon = $request->diskon;

        DB::update("UPDATE t_penjualan_detail
                            SET qty=?, diskon=?
                           WHERE no_transaksi=? AND kd_barang=? AND kd_satuan=?", ["$qty", "$diskon", "$no_transaksi", "$kd_barang", "$kd_satuan"]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Detail penjualan berhasil diperbarui.']);
        }
        return redirect()->route('index.penjualan');
    }

    public function hapusPenjualan(Request $request)
    {
        $no_transaksi = $request->no_transaksi;

        if (empty($no_transaksi)) {
            return response()->json(['success' => false, 'message' => 'No. transaksi tidak valid.'], 422);
        }

        try {
            DB::transaction(function () use ($no_transaksi) {
                DB::delete("DELETE FROM t_penjualan_detail WHERE no_transaksi = ?", [$no_transaksi]);
                DB::delete("DELETE FROM t_penjualan WHERE no_transaksi = ?", [$no_transaksi]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus transaksi.'], 500);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaksi penjualan berhasil dihapus.']);
        }
        return redirect()->route('index.penjualan');
    }
}
