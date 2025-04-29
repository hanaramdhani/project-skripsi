<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerJabatan extends Controller
{
    public function viewMasterJabatan()
    {
        $data = DB::select("SELECT * FROM m_jabatan");
        
        $kd_jabatan_temporary = DB::select("SELECT kd_jabatan FROM m_jabatan ORDER BY kd_jabatan DESC  LIMIT 1");
        $kd_jb = substr($kd_jabatan_temporary[0]->kd_jabatan, -3);
        $incremented = str_pad((int)$kd_jb + 1, 3, '0', STR_PAD_LEFT);
        $kd_jabatan = 'JAA' . $incremented;
        return view('jabatan', ['data' => $data, 'kd_jabatan' => $kd_jabatan]);
    }

    public function inputJabatan(Request $request)
    {
        $kd_jabatan = $request->kd_jabatan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_jabatan 
                    (kd_jabatan, nama, keterangan, `status`)
                    VALUES ('$kd_jabatan', '$nama', '$keterangan', '$status')");
        return redirect()->route('index.master.jabatan');
    }

    public function editJabatan(Request $request)
    {
        $kd_jabatan = $request->edit_kd_jabatan;
        $nama = $request->edit_nama_jabatan;
        $keterangan = $request->edit_keterangan_jabatan;
        $status = $request->edit_status_jabatan;

        DB::update("UPDATE m_jabatan SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_jabatan='$kd_jabatan'");
        return redirect()->route('index.master.jabatan');
    }

    public function hapusJabatan(Request $request)
    {
        $kd_jabatan = $request->hapus_kd_jabatan;
        DB::delete("DELETE FROM m_jabatan WHERE kd_jabatan='$kd_jabatan'");
        return redirect()->route('index.master.jabatan');
    }
}

