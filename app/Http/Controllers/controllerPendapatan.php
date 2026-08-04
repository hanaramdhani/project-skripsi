<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPendapatan extends Controller
{
    public function viewMasterPendapatan()
    {
        $kd_pendapatan_temporary = DB::select("SELECT TOP 1 kd_pendapatan FROM m_pendapatan ORDER BY kd_pendapatan DESC");
        $kd_ak = substr($kd_pendapatan_temporary[0]->kd_pendapatan, -3);
        $incremented = str_pad((int)$kd_ak + 1, 3, '0', STR_PAD_LEFT);
        $kd_pendapatan = 'PAA' . $incremented;

        return view('pendapatan', ['kd_pendapatan' => $kd_pendapatan]);
    }

    public function getDataPendapatan(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_pendapatan',
            1 => 'nama',
            2 => '[status]',
            3 => 'keterangan',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_pendapatan';
        if ($length <= 0) { $length = 10; }

        $from = "FROM m_pendapatan";

        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_pendapatan LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_pendapatan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    kd_pendapatan,
                    nama AS pendapatan,
                    [status],
                    keterangan
                $from $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputPendapatan(Request $request)
    {
        $kd_pendapatan = $request->kd_pendapatan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_pendapatan
                    (kd_pendapatan, nama, keterangan, [status])
                    VALUES (?, ?, ?, ?)", [$kd_pendapatan, $nama, $keterangan, $status]);
        return redirect()->route('index.master.pendapatan');
    }

    public function editPendapatan(Request $request)
    {
        $kd_pendapatan = $request->edit_kd_pendapatan;
        $nama = $request->edit_nama_pendapatan;
        $keterangan = $request->edit_keterangan_pendapatan;
        $status = $request->edit_status_pendapatan;

        DB::update("UPDATE m_pendapatan SET nama=?, keterangan=?, [status]=? WHERE kd_pendapatan=?", [$nama, $keterangan, $status, $kd_pendapatan]);
        return redirect()->route('index.master.pendapatan');
    }

    public function hapusPendapatan(Request $request)
    {
        $kd_pendapatan = $request->hapus_kd_pendapatan;
        DB::delete("DELETE FROM m_pendapatan WHERE kd_pendapatan=?", [$kd_pendapatan]);
        return redirect()->route('index.master.pendapatan');
    }
}

