<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPendapatan extends Controller
{
    public function viewMasterPendapatan()
    {
        $data = DB::select("SELECT 
                                kd_pendapatan,
                                m_pendapatan.nama AS pendapatan,
                                m_pendapatan.keterangan AS keterangan,
                                m_pendapatan.`status` AS `status`,
                                m_akun.kd_akun,
                                m_akun.nama AS akun
                            FROM m_pendapatan
                            INNER JOIN m_akun ON m_pendapatan.kd_akun = m_akun.kd_akun");
        
        $kd_pendapatan_temporary = DB::select("SELECT kd_pendapatan FROM m_pendapatan ORDER BY kd_pendapatan DESC  LIMIT 1");
        $kd_ak = substr($kd_pendapatan_temporary[0]->kd_pendapatan, -3);
        $incremented = str_pad((int)$kd_ak + 1, 3, '0', STR_PAD_LEFT);
        $kd_pendapatan = 'PAA' . $incremented;

        $akun = DB::select("SELECT 
                                kd_akun, 
                                nama AS akun
                            FROM m_akun WHERE `status`=1");
        return view('pendapatan', ['data' => $data, 'kd_pendapatan' => $kd_pendapatan, 'akun' => $akun]);
    }

    public function inputPendapatan(Request $request)
    {
        $kd_pendapatan = $request->kd_pendapatan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;
        $kd_akun = $request->kd_akun;

        DB::insert("INSERT INTO m_pendapatan 
                    (kd_pendapatan, kd_akun, nama, keterangan, `status`)
                    VALUES ('$kd_pendapatan', '$kd_akun', '$nama', '$keterangan', '$status')");
        return redirect()->route('index.master.pendapatan');
    }

    public function editGetAkun()
    {
        $akun = DB::select("SELECT kd_akun, nama FROM m_akun WHERE `status` = 1");
        return response()->json(['akun' => $akun]);
    }

    public function editPendapatan(Request $request)
    {
        $kd_pendapatan = $request->edit_kd_pendapatan;
        $nama = $request->edit_nama_pendapatan;
        $keterangan = $request->edit_keterangan_pendapatan;
        $status = $request->edit_status_pendapatan;
        $kd_akun = $request->edit_kd_akun;

        DB::update("UPDATE m_pendapatan SET kd_akun='$kd_akun', nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_pendapatan='$kd_pendapatan'");
        return redirect()->route('index.master.pendapatan');
    }

    public function hapusPendapatan(Request $request)
    {
        $kd_pendapatan = $request->hapus_kd_pendapatan;
        DB::delete("DELETE FROM m_pendapatan WHERE kd_pendapatan='$kd_pendapatan'");
        return redirect()->route('index.master.pendapatan');
    }
}

