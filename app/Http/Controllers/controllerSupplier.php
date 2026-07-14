<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerSupplier extends Controller
{
    public function viewMasterSupplier()
    {
        $kd_supplier_temporary = DB::select("SELECT TOP 1 kd_supplier FROM m_supplier ORDER BY kd_supplier DESC");
        $kd_sp = substr($kd_supplier_temporary[0]->kd_supplier, -3);
        $incremented = str_pad((int)$kd_sp + 1, 3, '0', STR_PAD_LEFT);
        $kd_supplier = 'SAA' . $incremented;
        return view('supplier', ['kd_supplier' => $kd_supplier]);
    }

    public function getDataSupplier(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_supplier',
            1 => 'nama',
            2 => 'alamat',
            3 => 'hp',
            4 => 'email',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_supplier';

        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_supplier LIKE ? OR nama LIKE ? OR alamat LIKE ? OR hp LIKE ? OR email LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_supplier")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_supplier $whereSql", $bindings)[0]->c;

        $sql = "SELECT kd_supplier, nama, alamat, hp AS no_hp, email
                FROM m_supplier
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function inputSupplier(Request $request)
    {
        $kd_supplier = $request->kd_supplier;
        $nama = $request->nama;
        $alamat = $request->alamat;
        $no_hp = $request->no_hp;
        $email = $request->email;

        DB::insert("INSERT INTO m_supplier
                    (kd_supplier, nama, alamat, hp, email, date_add)
                    VALUES (?, ?, ?, ?, ?, GETDATE())", [$kd_supplier, $nama, $alamat, $no_hp, $email]);
        return redirect()->route('index.master.supplier');
    }

    public function editSupplier(Request $request)
    {
        $kd_supplier = $request->edit_kd_supplier;
        $nama = $request->edit_nama_supplier;
        $alamat = $request->edit_alamat_supplier;
        $nohp = $request->edit_nohp_supplier;
        $email = $request->edit_email_supplier;

        DB::update("UPDATE m_supplier SET nama=?, alamat=?, hp=?, email=? WHERE kd_supplier=?", [$nama, $alamat, $nohp, $email, $kd_supplier]);
        return redirect()->route('index.master.supplier');
    }

    public function hapusSupplier(Request $request)
    {
        $kd_supplier = $request->hapus_kd_supplier;
        DB::delete("DELETE FROM m_supplier WHERE kd_supplier=?", [$kd_supplier]);
        return redirect()->route('index.master.supplier');
    }
}

