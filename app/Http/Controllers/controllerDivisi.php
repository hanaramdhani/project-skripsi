<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerDivisi extends Controller
{
    public function viewMasterDivisi()
    {
        $kd_divisi_temporary = DB::select("SELECT TOP 1 kd_divisi FROM m_divisi ORDER BY kd_divisi DESC");
        $kd_di = substr($kd_divisi_temporary[0]->kd_divisi, -3);
        $incremented = str_pad((int)$kd_di + 1, 3, '0', STR_PAD_LEFT);
        $kd_divisi = 'DAA' . $incremented;
        return view('divisi', ['kd_divisi' => $kd_divisi]);
    }

    public function getDataDivisi(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [ 0 => 'kd_divisi', 1 => 'nama', 2 => 'status', 3 => 'keterangan' ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_divisi';
        if ($length <= 0) { $length = 10; }
        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_divisi LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_divisi")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_divisi $whereSql", $bindings)[0]->c;
        $sql = "SELECT kd_divisi, nama, status, keterangan FROM m_divisi $whereSql
                ORDER BY $orderColumn $orderDir OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);
        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputDivisi(Request $request)
    {
        $kd_divisi = $request->kd_divisi;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_divisi
                    (kd_divisi, nama, keterangan, [status], date_add)
                    VALUES (?, ?, ?, ?, GETDATE())", [$kd_divisi, $nama, $keterangan, $status]);
        return redirect()->route('index.master.divisi');
    }

    public function editDivisi(Request $request)
    {
        $kd_divisi = $request->edit_kd_divisi;
        $nama = $request->edit_nama_divisi;
        $keterangan = $request->edit_keterangan_divisi;
        $status = $request->edit_status_divisi;

        DB::update("UPDATE m_divisi SET nama=?, keterangan=?, [status]=? WHERE kd_divisi=?", [$nama, $keterangan, $status, $kd_divisi]);
        return redirect()->route('index.master.divisi');
    }

    public function hapusDivisi(Request $request)
    {
        $kd_divisi = $request->hapus_kd_divisi;
        DB::delete("DELETE FROM m_divisi WHERE kd_divisi=?", [$kd_divisi]);
        return redirect()->route('index.master.divisi');
    }
}

