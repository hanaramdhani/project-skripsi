<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerDivisi extends Controller
{
    public function viewMasterDivisi()
    {
        $data = DB::select("SELECT * FROM m_divisi");
        
        $kd_divisi_temporary = DB::select("SELECT kd_divisi FROM m_divisi ORDER BY kd_divisi DESC  LIMIT 1");
        $kd_di = substr($kd_divisi_temporary[0]->kd_divisi, -3);
        $incremented = str_pad((int)$kd_di + 1, 3, '0', STR_PAD_LEFT);
        $kd_divisi = 'DAA' . $incremented;
        return view('divisi', ['data' => $data, 'kd_divisi' => $kd_divisi]);
    }

    public function inputDivisi(Request $request)
    {
        $kd_divisi = $request->kd_divisi;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_divisi 
                    (kd_divisi, nama, keterangan, `status`, date_add)
                    VALUES ('$kd_divisi', '$nama', '$keterangan', '$status', NOW())");
        return redirect()->route('index.master.divisi');
    }

    public function editDivisi(Request $request)
    {
        $kd_divisi = $request->edit_kd_divisi;
        $nama = $request->edit_nama_divisi;
        $keterangan = $request->edit_keterangan_divisi;
        $status = $request->edit_status_divisi;

        DB::update("UPDATE m_divisi SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_divisi='$kd_divisi'");
        return redirect()->route('index.master.divisi');
    }

    public function hapusDivisi(Request $request)
    {
        $kd_divisi = $request->hapus_kd_divisi;
        DB::delete("DELETE FROM m_divisi WHERE kd_divisi='$kd_divisi'");
        return redirect()->route('index.master.divisi');
    }
}

