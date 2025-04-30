<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPegawai extends Controller
{
    public function viewMasterPegawai()
    {
        $data = DB::select("SELECT 
                                kd_pegawai,
                                m_pegawai.nama AS pegawai,
                                m_pegawai.keterangan AS keterangan,
                                m_pegawai.`status` AS status_pegawai,
                                m_jabatan.kd_jabatan AS kd_jabatan,
                                m_jabatan.nama AS jabatan
                            FROM m_pegawai
                            INNER JOIN m_jabatan ON m_pegawai.kd_jabatan = m_jabatan.kd_jabatan");
        $jabatan = DB::select("SELECT 
                                    kd_jabatan, 
                                    nama AS jabatan
                                FROM m_jabatan WHERE `status` = 1");
        $kd_pegawai_temporary = DB::select("SELECT kd_pegawai FROM m_pegawai ORDER BY kd_pegawai DESC  LIMIT 1");
        $kd_pg = substr($kd_pegawai_temporary[0]->kd_pegawai, -3);
        $incremented = str_pad((int)$kd_pg + 1, 3, '0', STR_PAD_LEFT);
        $kd_pegawai = 'PAA' . $incremented;


        return view('pegawai', ['data' => $data, 'jabatan' => $jabatan, 'kd_pegawai' => $kd_pegawai]);
    }

    public function inputPegawai(Request $request)
    {
        $kd_pegawai = $request->kd_pegawai;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $kd_jabatan = $request->kd_jabatan;
        $status = $request->status;

        DB::insert("INSERT INTO m_pegawai 
                    (kd_pegawai, nama, keterangan, kd_jabatan, `status`)
                    VALUES ('$kd_pegawai', '$nama', '$keterangan', '$kd_jabatan', '$status')");
        return redirect()->route('index.master.pegawai');
    }

    public function getJabatanPegawai()
    {
        $jabatan = DB::select("SELECT 
                                kd_jabatan, 
                                nama 
                            FROM m_jabatan WHERE `status` = 1");
        return response()->json(['jabatan' => $jabatan]);
    }

    public function editPegawai(Request $request)
    {
        $kd_pegawai = $request->edit_kd_pegawai;
        $nama = $request->edit_nama_pegawai;
        $keterangan = $request->edit_keterangan_pegawai;
        $status = $request->edit_status_pegawai;
        $kd_jabatan = $request->edit_kdJabatan_pegawai;

        // print_r($request->edit_kdJabatan_pegawai);

        DB::update("UPDATE m_pegawai SET nama='$nama', keterangan='$keterangan', `status`='$status', kd_jabatan='$kd_jabatan' WHERE kd_pegawai='$kd_pegawai'");
        return redirect()->route('index.master.pegawai');
    }

    public function hapusPegawai(Request $request)
    {
        $kd_pegawai = $request->hapus_kd_pegawai;
        DB::delete("DELETE FROM m_pegawai WHERE kd_pegawai='$kd_pegawai'");
        return redirect()->route('index.master.pegawai');
    }
}

