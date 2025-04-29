<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerSupplier extends Controller
{
    public function viewMasterSupplier()
    {
        $data = DB::select("SELECT * FROM m_supplier");
        
        $kd_supplier_temporary = DB::select("SELECT kd_supplier FROM m_supplier ORDER BY kd_supplier DESC  LIMIT 1");
        $kd_sp = substr($kd_supplier_temporary[0]->kd_supplier, -3);
        $incremented = str_pad((int)$kd_sp + 1, 3, '0', STR_PAD_LEFT);
        $kd_supplier = 'SAA' . $incremented;
        return view('supplier', ['data' => $data, 'kd_supplier' => $kd_supplier]);
    }

    public function inputSupplier(Request $request)
    {
        $kd_supplier = $request->kd_supplier;
        $nama = $request->nama;
        $alamat = $request->alamat;
        $no_hp = $request->no_hp;
        $email = $request->email;

        DB::insert("INSERT INTO m_supplier 
                    (kd_supplier, nama, alamat, no_hp, email, date_add)
                    VALUES ('$kd_supplier', '$nama', '$alamat', '$no_hp', '$email', NOW())");
        return redirect()->route('index.master.supplier');
    }

    public function editSupplier(Request $request)
    {
        $kd_supplier = $request->edit_kd_supplier;
        $nama = $request->edit_nama_supplier;
        $alamat = $request->edit_alamat_supplier;
        $nohp = $request->edit_nohp_supplier;
        $email = $request->edit_email_supplier;

        DB::update("UPDATE m_supplier SET nama='$nama', alamat='$alamat', no_hp='$nohp', email='$email' WHERE kd_supplier='$kd_supplier'");
        return redirect()->route('index.master.supplier');
    }

    public function hapusSupplier(Request $request)
    {
        $kd_supplier = $request->hapus_kd_supplier;
        DB::delete("DELETE FROM m_supplier WHERE kd_supplier='$kd_supplier'");
        return redirect()->route('index.master.supplier');
    }
}

