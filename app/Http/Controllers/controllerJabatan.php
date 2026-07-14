<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerJabatan extends Controller
{
    public function viewMasterJabatan()
    {
        $kd_jabatan_temporary = DB::select("SELECT TOP 1 kd_jabatan FROM m_jabatan ORDER BY kd_jabatan DESC");
        $kd_jb = substr($kd_jabatan_temporary[0]->kd_jabatan, -3);
        $incremented = str_pad((int)$kd_jb + 1, 3, '0', STR_PAD_LEFT);
        $kd_jabatan = 'JAA' . $incremented;
        return view('jabatan', ['kd_jabatan' => $kd_jabatan]);
    }

    public function getDataJabatan(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [ 0 => 'kd_jabatan', 1 => 'nama', 2 => 'status', 3 => 'keterangan' ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_jabatan';
        if ($length <= 0) { $length = 10; }
        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_jabatan LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_jabatan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_jabatan $whereSql", $bindings)[0]->c;
        $sql = "SELECT kd_jabatan, nama, [status] AS status, keterangan FROM m_jabatan $whereSql
                ORDER BY $orderColumn $orderDir OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);
        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputJabatan(Request $request)
    {
        $kd_jabatan = $request->kd_jabatan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_jabatan
                    (kd_jabatan, nama, keterangan, [status])
                    VALUES (?, ?, ?, ?)", [$kd_jabatan, $nama, $keterangan, $status]);
        return redirect()->route('index.master.jabatan');
    }

    public function editJabatan(Request $request)
    {
        $kd_jabatan = $request->edit_kd_jabatan;
        $nama = $request->edit_nama_jabatan;
        $keterangan = $request->edit_keterangan_jabatan;
        $status = $request->edit_status_jabatan;

        DB::update("UPDATE m_jabatan SET nama=?, keterangan=?, [status]=? WHERE kd_jabatan=?", [$nama, $keterangan, $status, $kd_jabatan]);
        return redirect()->route('index.master.jabatan');
    }

    public function hapusJabatan(Request $request)
    {
        $kd_jabatan = $request->hapus_kd_jabatan;
        DB::delete("DELETE FROM m_jabatan WHERE kd_jabatan=?", [$kd_jabatan]);
        return redirect()->route('index.master.jabatan');
    }
}

