<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerKas extends Controller
{
    public function viewMasterKas()
    {
        $data = DB::select("SELECT * FROM m_kas");
        
        $kd_kas_temporary = DB::select("SELECT kd_kas FROM m_kas ORDER BY kd_kas DESC  LIMIT 1");
        $kd_ka = substr($kd_kas_temporary[0]->kd_kas, -3);
        $incremented = str_pad((int)$kd_ka + 1, 3, '0', STR_PAD_LEFT);
        $kd_kas = 'KAA' . $incremented;
        return view('kas', ['data' => $data, 'kd_kas' => $kd_kas]);
    }

    public function inputKas(Request $request)
    {
        $kd_kas = $request->kd_kas;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_kas 
                    (kd_kas, nama, keterangan, `status`, date_add)
                    VALUES ('$kd_kas', '$nama', '$keterangan', '$status', NOW())");
        return redirect()->route('index.master.kas');
    }

    public function editKas(Request $request)
    {
        $kd_kas = $request->edit_kd_kas;
        $nama = $request->edit_nama_kas;
        $keterangan = $request->edit_keterangan_kas;
        $status = $request->edit_status_kas;

        DB::update("UPDATE m_kas SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_kas='$kd_kas'");
        return redirect()->route('index.master.kas');
    }

    public function hapusKas(Request $request)
    {
        $kd_kas = $request->hapus_kd_kas;
        DB::delete("DELETE FROM m_kas WHERE kd_kas='$kd_kas'");
        return redirect()->route('index.master.kas');
    }
}

