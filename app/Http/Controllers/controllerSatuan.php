<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerSatuan extends Controller
{
    public function viewMasterSatuan()
    {
        $data = DB::select("SELECT * FROM m_satuan");
        
        $kd_satuan_temporary = DB::select("SELECT kd_satuan FROM m_satuan ORDER BY kd_satuan DESC  LIMIT 1");
        $kd_st = substr($kd_satuan_temporary[0]->kd_satuan, -3);
        $incremented = str_pad((int)$kd_st + 1, 3, '0', STR_PAD_LEFT);
        $kd_satuan = 'SAA' . $incremented;
        return view('Satuan', ['data' => $data, 'kd_satuan' => $kd_satuan]);
    }

    public function inputSatuan(Request $request)
    {
        $kd_satuan = $request->kd_satuan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_satuan 
                    (kd_satuan, nama, keterangan, `status`, date_add)
                    VALUES ('$kd_satuan', '$nama', '$keterangan', '$status', NOW())");
        return redirect()->route('index.master.satuan');
    }

    public function editSatuan(Request $request)
    {
        $kd_satuan = $request->edit_kd_satuan;
        $nama = $request->edit_nama_satuan;
        $keterangan = $request->edit_keterangan_satuan;
        $status = $request->edit_status_satuan;

        DB::update("UPDATE m_satuan SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_satuan='$kd_satuan'");
        return redirect()->route('index.master.satuan');
    }

    public function hapusSatuan(Request $request)
    {
        $kd_satuan = $request->hapus_kd_satuan;
        DB::delete("DELETE FROM m_satuan WHERE kd_satuan='$kd_satuan'");
        return redirect()->route('index.master.satuan');
    }
}

