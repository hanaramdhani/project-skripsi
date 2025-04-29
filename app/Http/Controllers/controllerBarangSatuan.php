<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBarangSatuan extends Controller
{
    public function viewMasterBarangSatuan()
    {
        $data = DB::select("SELECT 
                                m_barang.kd_barang AS kd_barang,
                                m_barang.nama AS barang,
                                m_satuan.kd_satuan AS kd_satuan,
                                m_satuan.nama AS satuan,
                                m_barang_satuan.harga_jual AS harga_jual,
                                m_barang_satuan.keterangan AS keterangan,
                                m_barang_satuan.`status` AS `status`
                            FROM m_barang_satuan
                            INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                            INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan
                            ORDER BY m_barang.nama");
        $barang = DB::select("SELECT 
                                    kd_barang, 
                                    nama AS barang
                                FROM m_barang");
        $satuan = DB::select("SELECT 
                                    kd_satuan, 
                                    nama AS satuan
                                FROM m_satuan");
        return view('BarangSatuan', ['data' => $data, 'barang' => $barang, 'satuan' => $satuan]);
    }

    public function inputBarangSatuan(Request $request)
    {
        $kd_barang = $request->kd_barang;
        $kd_satuan = $request->kd_satuan;
        $harga_jual = $request->harga_jual;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_barang_satuan 
                    (kd_barang, kd_satuan, harga_jual, keterangan, `status`)
                    VALUES ('$kd_barang', '$kd_satuan', '$harga_jual', '$keterangan', '$status')");
        return redirect()->route('index.master.barang.satuan');
    }

    public function getBarangSatuanEdit()
    {
        $barang = DB::select("SELECT 
                                kd_barang, 
                                nama 
                            FROM m_barang");
        $satuan = DB::select("SELECT 
                                kd_satuan, 
                                nama 
                            FROM m_satuan");
        return response()->json(['barang' => $barang, 'satuan' => $satuan]);
    }

    public function editBarangSatuan(Request $request)
    {
        $kd_barang = $request->edit_kd_barang;
        $kd_satuan = $request->edit_kd_satuan;
        $harga_jual = $request->edit_harga_jual;
        $keterangan = $request->edit_keterangan;
        $status = $request->edit_status;
        // print_r("UPDATE m_barang_satuan SET harga_jual='$harga_jual', keterangan='$keterangan', `status`='$status' WHERE kd_barang='$kd_barang' AND kd_satuan='$kd_satuan'");

        DB::update("UPDATE m_barang_satuan SET harga_jual='$harga_jual', keterangan='$keterangan', `status`='$status' WHERE kd_barang='$kd_barang' AND kd_satuan='$kd_satuan'");
        return redirect()->route('index.master.barang.satuan');
    }

    public function hapusBarangSatuan(Request $request)
    {
        $kd_barang = $request->hapus_kd_barang;
        $kd_satuan = $request->hapus_kd_satuan;
        DB::delete("DELETE FROM m_barang_satuan WHERE kd_barang='$kd_barang' AND kd_satuan='$kd_satuan'");
        return redirect()->route('index.master.barang.satuan');
    }
}

