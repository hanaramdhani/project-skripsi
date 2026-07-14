<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerKas extends Controller
{
    public function viewMasterKas()
    {
        $kd_kas_temporary = DB::select("SELECT TOP 1 kd_kas FROM m_kas ORDER BY kd_kas DESC");
        $kd_ka = substr($kd_kas_temporary[0]->kd_kas, -3);
        $incremented = str_pad((int)$kd_ka + 1, 3, '0', STR_PAD_LEFT);
        $kd_kas = 'KAA' . $incremented;
        return view('kas', ['kd_kas' => $kd_kas]);
    }

    public function getDataKas(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [ 0 => 'kd_kas', 1 => 'nama', 2 => 'status', 3 => 'keterangan' ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_kas';
        if ($length <= 0) { $length = 10; }
        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_kas LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_kas")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_kas $whereSql", $bindings)[0]->c;
        $sql = "SELECT kd_kas, nama, status, keterangan FROM m_kas $whereSql
                ORDER BY $orderColumn $orderDir OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);
        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputKas(Request $request)
    {
        $kd_kas = $request->kd_kas;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_kas
                    (kd_kas, nama, keterangan, [status], date_add)
                    VALUES (?, ?, ?, ?, GETDATE())", [$kd_kas, $nama, $keterangan, $status]);
        return redirect()->route('index.master.kas');
    }

    public function editKas(Request $request)
    {
        $kd_kas = $request->edit_kd_kas;
        $nama = $request->edit_nama_kas;
        $keterangan = $request->edit_keterangan_kas;
        $status = $request->edit_status_kas;

        DB::update("UPDATE m_kas SET nama=?, keterangan=?, [status]=? WHERE kd_kas=?", [$nama, $keterangan, $status, $kd_kas]);
        return redirect()->route('index.master.kas');
    }

    public function hapusKas(Request $request)
    {
        $kd_kas = $request->hapus_kd_kas;
        DB::delete("DELETE FROM m_kas WHERE kd_kas=?", [$kd_kas]);
        return redirect()->route('index.master.kas');
    }
}

