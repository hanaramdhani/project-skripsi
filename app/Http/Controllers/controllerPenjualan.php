<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPenjualan extends Controller
{
    public function viewPenjualan(){
        $data = DB::select("SELECT 
                                t_penjualan.no_transaksi,
                                t_penjualan.tanggal,
                                m_barang.nama AS barang,
                                m_satuan.nama AS satuan,
                                harga_jual,
                                qty,
                                harga_jual*qty AS total
                            FROM t_penjualan 
                            INNER JOIN t_penjualan_detail ON t_penjualan.no_transaksi = t_penjualan_detail.no_transaksi
                            INNER JOIN m_barang ON t_penjualan_detail.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON t_penjualan_detail.kd_satuan = m_satuan.kd_satuan");
        return view('Penjualan', ['data' => $data]);
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
        $diskon = $request->diskon;


        DB::insert("INSERT INTO t_penjualan 
        (no_transaksi, kd_customer, kd_pegawai, kd_divisi, kd_kas, tanggal, diskon, keterangan, `status`)
        VALUES
        ('$no_transaksi', '$kd_customer', '$kd_pegawai', '-', '-', NOW(), $diskon, '-', 0)
        ");

        $products = $request->products;
        foreach ($products as $product) {
            $kd_barang = $product['kd_barang'];
            $kd_satuan = $product['kd_satuan'];
            $qty = $product['qty'];
            $harga_jual = $product['harga_jual'];

            DB::insert("INSERT INTO t_penjualan_detail 
                    (no_transaksi, kd_barang, kd_satuan, jenis, harga_jual,qty, diskon, keterangan, `status`)
                    VALUES
                    ('$no_transaksi', '$kd_barang', '$kd_satuan', '1', '$harga_jual', '$qty', 0, '-', '1')");
        }        
        return $this->viewPenjualan();
    }
}
