<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerSatuan extends Controller
{
    public function viewMasterSatuan()
    {
        $kd_satuan_temporary = DB::select("SELECT TOP 1 kd_satuan FROM m_satuan ORDER BY kd_satuan DESC");
        $kd_st = substr($kd_satuan_temporary[0]->kd_satuan, -3);
        $incremented = str_pad((int)$kd_st + 1, 3, '0', STR_PAD_LEFT);
        $kd_satuan = 'SAA' . $incremented;
        return view('Satuan', ['kd_satuan' => $kd_satuan]);
    }

    public function getDataSatuan(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [ 0 => 'kd_satuan', 1 => 'nama', 2 => 'status', 3 => 'keterangan' ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_satuan';
        if ($length <= 0) { $length = 10; }
        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_satuan LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_satuan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_satuan $whereSql", $bindings)[0]->c;
        $sql = "SELECT kd_satuan, nama, status, keterangan FROM m_satuan $whereSql
                ORDER BY $orderColumn $orderDir OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);
        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputSatuan(Request $request)
    {
        $kd_satuan = $request->kd_satuan;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_satuan
                    (kd_satuan, nama, keterangan, [status], date_add)
                    VALUES (?, ?, ?, ?, GETDATE())", [$kd_satuan, $nama, $keterangan, $status]);
        return redirect()->route('index.master.satuan');
    }

    public function editSatuan(Request $request)
    {
        $kd_satuan = $request->edit_kd_satuan;
        $nama = $request->edit_nama_satuan;
        $keterangan = $request->edit_keterangan_satuan;
        $status = $request->edit_status_satuan;

        DB::update("UPDATE m_satuan SET nama=?, keterangan=?, [status]=? WHERE kd_satuan=?", [$nama, $keterangan, $status, $kd_satuan]);
        return redirect()->route('index.master.satuan');
    }

    public function hapusSatuan(Request $request)
    {
        $kd_satuan = $request->hapus_kd_satuan;
        DB::delete("DELETE FROM m_satuan WHERE kd_satuan=?", [$kd_satuan]);
        return redirect()->route('index.master.satuan');
    }
}

