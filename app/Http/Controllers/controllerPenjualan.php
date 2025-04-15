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
}
