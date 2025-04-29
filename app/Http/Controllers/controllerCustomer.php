<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerCustomer extends Controller
{
    public function viewMasterCustomer()
    {
        $data = DB::select("SELECT * FROM m_customer");
        
        $kd_customer_temporary = DB::select("SELECT kd_customer FROM m_customer ORDER BY kd_customer DESC  LIMIT 1");
        $kd_cs = substr($kd_customer_temporary[0]->kd_customer, -3);
        $incremented = str_pad((int)$kd_cs + 1, 3, '0', STR_PAD_LEFT);
        $kd_customer = 'CAA' . $incremented;
        return view('customer', ['data' => $data, 'kd_customer' => $kd_customer]);
    }

    public function inputCustomer(Request $request)
    {
        $kd_customer = $request->kd_customer;
        $nama = $request->nama;
        $alamat = $request->alamat;
        $no_hp = $request->no_hp;
        $email = $request->email;

        DB::insert("INSERT INTO m_customer 
                    (kd_customer, nama, alamat, no_hp, email, date_add)
                    VALUES ('$kd_customer', '$nama', '$alamat', '$no_hp', '$email', NOW())");
        return redirect()->route('index.master.customer');
    }

    public function editCustomer(Request $request)
    {
        $kd_customer = $request->edit_kd_customer;
        $nama = $request->edit_nama_customer;
        $alamat = $request->edit_alamat_customer;
        $nohp = $request->edit_nohp_customer;
        $email = $request->edit_email_customer;

        DB::update("UPDATE m_customer SET nama='$nama', alamat='$alamat', no_hp='$nohp', email='$email' WHERE kd_customer='$kd_customer'");
        return redirect()->route('index.master.customer');
    }

    public function hapusCustomer(Request $request)
    {
        $kd_customer = $request->hapus_kd_customer;
        DB::delete("DELETE FROM m_customer WHERE kd_customer='$kd_customer'");
        return redirect()->route('index.master.customer');
    }
}

