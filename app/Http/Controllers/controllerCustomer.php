<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerCustomer extends Controller
{
    public function viewMasterCustomer()
    {
        $kd_customer_temporary = DB::select("SELECT TOP 1 kd_customer FROM m_customer ORDER BY kd_customer DESC");
        $kd_cs = substr($kd_customer_temporary[0]->kd_customer, -3);
        $incremented = str_pad((int)$kd_cs + 1, 3, '0', STR_PAD_LEFT);
        $kd_customer = 'CAA' . $incremented;
        return view('customer', ['kd_customer' => $kd_customer]);
    }

    public function getDataCustomer(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_customer',
            1 => 'nama',
            2 => 'alamat',
            3 => 'hp',
            4 => 'email',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_customer';

        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_customer LIKE ? OR nama LIKE ? OR alamat LIKE ? OR hp LIKE ? OR email LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_customer")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_customer $whereSql", $bindings)[0]->c;

        $sql = "SELECT kd_customer, nama, alamat, hp AS no_hp, email
                FROM m_customer
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function inputCustomer(Request $request)
    {
        $kd_customer = $request->kd_customer;
        $nama = $request->nama;
        $alamat = $request->alamat;
        $no_hp = $request->no_hp;
        $email = $request->email;

        DB::insert("INSERT INTO m_customer
                    (kd_customer, nama, alamat, hp, email, date_add)
                    VALUES (?, ?, ?, ?, ?, GETDATE())", [$kd_customer, $nama, $alamat, $no_hp, $email]);
        return redirect()->route('index.master.customer');
    }

    public function editCustomer(Request $request)
    {
        $kd_customer = $request->edit_kd_customer;
        $nama = $request->edit_nama_customer;
        $alamat = $request->edit_alamat_customer;
        $nohp = $request->edit_nohp_customer;
        $email = $request->edit_email_customer;

        DB::update("UPDATE m_customer SET nama=?, alamat=?, hp=?, email=? WHERE kd_customer=?", [$nama, $alamat, $nohp, $email, $kd_customer]);
        return redirect()->route('index.master.customer');
    }

    public function hapusCustomer(Request $request)
    {
        $kd_customer = $request->hapus_kd_customer;
        DB::delete("DELETE FROM m_customer WHERE kd_customer=?", [$kd_customer]);
        return redirect()->route('index.master.customer');
    }
}

