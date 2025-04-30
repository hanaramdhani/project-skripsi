<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerAkun extends Controller
{
    public function viewMasterAkun()
    {
        $data = DB::select("SELECT * FROM m_akun");
        
        $kd_akun_temporary = DB::select("SELECT kd_akun FROM m_akun ORDER BY kd_akun DESC  LIMIT 1");
        $kd_ak = substr($kd_akun_temporary[0]->kd_akun, -3);
        $incremented = str_pad((int)$kd_ak + 1, 3, '0', STR_PAD_LEFT);
        $kd_akun = 'AAA' . $incremented;
        return view('akun', ['data' => $data, 'kd_akun' => $kd_akun]);
    }

    public function inputAkun(Request $request)
    {
        $kd_akun = $request->kd_akun;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_akun 
                    (kd_akun, nama, keterangan, `status`)
                    VALUES ('$kd_akun', '$nama', '$keterangan', '$status')");
        return redirect()->route('index.master.akun');
    }

    public function editAkun(Request $request)
    {
        $kd_akun = $request->edit_kd_akun;
        $nama = $request->edit_nama_akun;
        $keterangan = $request->edit_keterangan_akun;
        $status = $request->edit_status_akun;

        DB::update("UPDATE m_akun SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_akun='$kd_akun'");
        return redirect()->route('index.master.akun');
    }

    public function hapusAkun(Request $request)
    {
        $kd_akun = $request->hapus_kd_akun;
        DB::delete("DELETE FROM m_akun WHERE kd_akun='$kd_akun'");
        return redirect()->route('index.master.akun');
    }
}

