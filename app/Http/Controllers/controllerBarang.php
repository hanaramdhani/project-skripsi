<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBarang extends Controller
{
    public function viewMasterBarang()
    {
        $data = DB::select("SELECT
                                kd_barang,
                                nama,
                                `status`,
                                 CONCAT(DAY(tanggal_daftar), ' ', 
                                    CASE MONTH(tanggal_daftar)
                                        WHEN 1 THEN 'Januari'
                                        WHEN 2 THEN 'Februari'
                                        WHEN 3 THEN 'Maret'
                                        WHEN 4 THEN 'April'
                                        WHEN 5 THEN 'Mei'
                                        WHEN 6 THEN 'Juni'
                                        WHEN 7 THEN 'Juli'
                                        WHEN 8 THEN 'Agustus'
                                        WHEN 9 THEN 'September'
                                        WHEN 10 THEN 'Oktober'
                                        WHEN 11 THEN 'November'
                                        WHEN 12 THEN 'Desember'
                                    END, 
                                    ' ', YEAR(tanggal_daftar)
                                ) AS tanggal_daftar,
                                keterangan
                            FROM m_barang");
        
        $kd_barang_temporary = DB::select("SELECT kd_barang FROM m_barang ORDER BY kd_barang DESC  LIMIT 1");
        $kd_br = substr($kd_barang_temporary[0]->kd_barang, -3);
        $incremented = str_pad((int)$kd_br + 1, 3, '0', STR_PAD_LEFT);
        $kd_barang = 'BAA' . $incremented;
        return view('Barang', ['data' => $data, 'kd_barang' => $kd_barang]);
    }

    public function inputBarang(Request $request)
    {
        $kd_barang = $request->kd_barang;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_barang 
                    (kd_barang, nama, keterangan, `status`, tanggal_daftar, date_add)
                    VALUES ('$kd_barang', '$nama', '$keterangan', '$status', NOW(), NOW())");
        return redirect()->route('index.master.barang');
    }

    public function editBarang(Request $request)
    {
        $kd_barang = $request->edit_kd_barang;
        $nama = $request->edit_nama_barang;
        $keterangan = $request->edit_keterangan_barang;
        $status = $request->edit_status_barang;

        DB::update("UPDATE m_barang SET nama='$nama', keterangan='$keterangan', `status`='$status' WHERE kd_barang='$kd_barang'");
        return redirect()->route('index.master.barang');
    }

    public function hapusBarang(Request $request)
    {
        $kd_barang = $request->hapus_kd_barang;
        DB::delete("DELETE FROM m_barang WHERE kd_barang='$kd_barang'");
        return redirect()->route('index.master.barang');
    }
}

