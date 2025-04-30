<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBiaya extends Controller
{
    public function viewMasterBiaya()
    {
        $data = DB::select("SELECT 
                                kd_biaya,
                                m_biaya.nama AS biaya,
                                m_biaya.keterangan AS keterangan,
                                m_biaya.`status` AS `status`,
                                m_akun.kd_akun,
                                m_akun.nama AS akun
                            FROM m_biaya
                            INNER JOIN m_akun ON m_biaya.kd_akun = m_akun.kd_akun");
        
        $kd_biaya_temporary = DB::select("SELECT kd_biaya FROM m_biaya ORDER BY kd_biaya DESC  LIMIT 1");
        $kd_ak = substr($kd_biaya_temporary[0]->kd_biaya, -3);
        $incremented = str_pad((int)$kd_ak + 1, 3, '0', STR_PAD_LEFT);
        $kd_biaya = 'BAA' . $incremented;

        $akun = DB::select("SELECT 
                                kd_akun, 
                                nama AS akun
                            FROM m_akun WHERE `status`=1");
        return view('biaya', ['data' => $data, 'kd_biaya' => $kd_biaya, 'akun' => $akun]);
    }

    public function inputBiaya(Request $request)
    {
        $kd_biaya = $request->kd_biaya;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;
        $kd_akun = $request->kd_akun;

        DB::insert("INSERT INTO m_biaya 
                    (kd_biaya, kd_akun, nama, keterangan, `status`)
                    VALUES ('$kd_biaya', '$kd_akun', '$nama', '$keterangan', '$status')");
        return redirect()->route('index.master.biaya');
    }

    public function editGetAkun()
    {
        $akun = DB::select("SELECT kd_akun, nama FROM m_akun WHERE `status` = 1");
        return response()->json(['akun' => $akun]);
    }

    public function editBiaya(Request $request)
    {
        $kd_biaya = $request->edit_kd_biaya;
        $nama = $request->edit_nama_biaya;
        $keterangan = $request->edit_keterangan_biaya;
        $status = $request->edit_status_biaya;
        $kd_akun = $request->edit_kd_akun;

        DB::update("UPDATE m_biaya SET kd_akun='$kd_akun', nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_biaya='$kd_biaya'");
        return redirect()->route('index.master.biaya');
    }

    public function hapusBiaya(Request $request)
    {
        $kd_biaya = $request->hapus_kd_biaya;
        DB::delete("DELETE FROM m_biaya WHERE kd_biaya='$kd_biaya'");
        return redirect()->route('index.master.biaya');
    }
}

