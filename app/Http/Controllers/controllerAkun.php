<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerAkun extends Controller
{
    public function viewMasterAkun()
    {
        $kd_akun_temporary = DB::select("SELECT TOP 1 coa_kode FROM m_coa ORDER BY coa_kode DESC");
        $kd_ak = substr($kd_akun_temporary[0]->coa_kode, -3);
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
            0 => 'coa_kode',
            1 => 'coa_nama',
            2 => 'coa_tipe',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'coa_kode';

        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(coa_kode LIKE ? OR coa_nama LIKE ? OR coa_tipe LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_coa")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_coa $whereSql", $bindings)[0]->c;

        $sql = "SELECT coa_kode AS kd_akun, coa_nama AS nama, coa_tipe AS tipe
                FROM m_coa
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
        $tipe = $request->tipe;

        DB::insert("INSERT INTO m_coa
                    (coa_kode, coa_nama, coa_tipe)
                    VALUES (?, ?, ?)", [$kd_akun, $nama, $tipe]);
        return redirect()->route('index.master.akun');
    }

    public function editAkun(Request $request)
    {
        $kd_akun = $request->edit_kd_akun;
        $nama = $request->edit_nama_akun;
        $tipe = $request->edit_tipe_akun;

        DB::update("UPDATE m_coa SET coa_nama=?, coa_tipe=? WHERE coa_kode=?", [$nama, $tipe, $kd_akun]);
        return redirect()->route('index.master.akun');
    }

    public function hapusAkun(Request $request)
    {
        $kd_akun = $request->hapus_kd_akun;
        DB::delete("DELETE FROM m_coa WHERE coa_kode=?", [$kd_akun]);
        return redirect()->route('index.master.akun');
    }
}

