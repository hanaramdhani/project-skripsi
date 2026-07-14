<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerAkun extends Controller
{
    public function viewMasterAkun()
    {
        $kd_akun_temporary = DB::select("SELECT TOP 1 kd_akun FROM m_akun ORDER BY kd_akun DESC");
        $kd_ak = substr($kd_akun_temporary[0]->kd_akun, -3);
        $incremented = str_pad((int)$kd_ak + 1, 3, '0', STR_PAD_LEFT);
        $kd_akun = 'AAA' . $incremented;
        return view('akun', ['kd_akun' => $kd_akun]);
    }

    public function getDataAkun(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_akun',
            1 => 'nama',
            2 => 'status',
            3 => 'keterangan',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_akun';

        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_akun LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_akun")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_akun $whereSql", $bindings)[0]->c;

        $sql = "SELECT kd_akun, nama, [status] AS status, keterangan
                FROM m_akun
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

    public function inputAkun(Request $request)
    {
        $kd_akun = $request->kd_akun;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_akun
                    (kd_akun, nama, keterangan, [status])
                    VALUES (?, ?, ?, ?)", [$kd_akun, $nama, $keterangan, $status]);
        return redirect()->route('index.master.akun');
    }

    public function editAkun(Request $request)
    {
        $kd_akun = $request->edit_kd_akun;
        $nama = $request->edit_nama_akun;
        $keterangan = $request->edit_keterangan_akun;
        $status = $request->edit_status_akun;

        DB::update("UPDATE m_akun SET nama=?, keterangan=?, [status]=? WHERE kd_akun=?", [$nama, $keterangan, $status, $kd_akun]);
        return redirect()->route('index.master.akun');
    }

    public function hapusAkun(Request $request)
    {
        $kd_akun = $request->hapus_kd_akun;
        DB::delete("DELETE FROM m_akun WHERE kd_akun=?", [$kd_akun]);
        return redirect()->route('index.master.akun');
    }
}

