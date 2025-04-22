<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPenjualan extends Controller
{
    public function viewPenjualan(){
        $data = DB::select("SELECT
                                no_transaksi,
                                CONCAT(DAY(tanggal), ' ', 
                                    CASE MONTH(tanggal)
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
                                    END, 
                                    ' ', YEAR(tanggal)
                                ) AS tanggal_penjualan,
                                diskon,
                                m_customer.nama AS customer
                            FROM t_penjualan
                            INNER JOIN m_customer ON t_penjualan.kd_customer = m_customer.kd_customer
                            ORDER BY no_transaksi");
        $customer = DB::select("SELECT 
                                    kd_customer,
                                    nama AS customer
                                FROM m_customer
                                ORDER BY nama");
        $no_transaksi_temporary = DB::select("SELECT no_transaksi FROM t_penjualan ORDER BY no_transaksi DESC LIMIT 1");
        $no_tr = substr($no_transaksi_temporary[0]->no_transaksi, -4);
        $incremented = str_pad((int)$no_tr + 1, 4, '0', STR_PAD_LEFT);
        $no_transaksi = 'PJ' . date('Ymd') . $incremented;
        
        return view('Penjualan', ['data' => $data, 'customer' => $customer, 'no_transaksi' => $no_transaksi]);
    }

    public function getBarangSatuan(Request $request){
        $keyword = $request->q; // assuming you're passing ?q=keyword

        $dataBarangSatuan = DB::select("SELECT 
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            WHERE m_barang.nama LIKE ? ORDER BY m_barang.nama", ["%$keyword%"]);
        return response()->json(['dataBarangSatuan'=>$dataBarangSatuan]);
    }

    public function inputPenjualan(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $kd_customer = $request->kd_customer;
        $kd_pegawai = $request->kd_pegawai;
        $masterDiskon = $request->masterDiskon;


        DB::insert("INSERT INTO t_penjualan 
        (no_transaksi, kd_customer, kd_pegawai, kd_divisi, kd_kas, tanggal, diskon, keterangan, `status`)
        VALUES
        ('$no_transaksi', '$kd_customer', '$kd_pegawai', '-', '-', NOW(), '$masterDiskon', '-', 0)
        ");

        $products = $request->products;
        foreach ($products as $product) {
            $kd_barang = $product['kd_barang'];
            $kd_satuan = $product['kd_satuan'];
            $qty = $product['qty'];
            $diskon_dt = $product['diskon_dt'];
            $harga_jual = $product['harga_jual'];

            DB::insert("INSERT INTO t_penjualan_detail 
                    (no_transaksi, kd_barang, kd_satuan, jenis, harga_jual,qty, diskon, keterangan, `status`)
                    VALUES
                    ('$no_transaksi', '$kd_barang', '$kd_satuan', '1', '$harga_jual', '$qty', '$diskon_dt', '-', '1')");
        }        
        return $this->viewPenjualan();
    }


    public function getDetailPenjualan(Request $request)
    {
        $keyword = $request->no_transaksi;

        $sql = DB::select("SELECT
                                m_barang.nama AS barang,
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
        // print_r("SELECT
        //                         m_barang.nama AS barang,
        //                         m_satuan.nama AS satuan,
        //                         harga_jual,
        //                         qty,
        //                         t_penjualan_detail.diskon AS diskon
        //                     FROM t_penjualan 
        //                     INNER JOIN t_penjualan_detail ON t_penjualan.no_transaksi = t_penjualan_detail.no_transaksi
        //                     INNER JOIN m_barang ON t_penjualan_detail.kd_barang = m_barang.kd_barang
        //                     INNER JOIN m_satuan ON t_penjualan_detail.kd_satuan = m_satuan.kd_satuan
        //                     WHERE t_penjualan.no_transaksi='$keyword'");
    }
}
