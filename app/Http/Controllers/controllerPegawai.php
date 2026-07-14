<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPegawai extends Controller
{
    public function viewMasterPegawai()
    {
        $jabatan = DB::select("SELECT 
                                    kd_jabatan, 
                                    nama AS jabatan
                                FROM m_jabatan WHERE [status] = 1");
        $kd_pegawai_temporary = DB::select("SELECT TOP 1 kd_pegawai FROM m_pegawai ORDER BY kd_pegawai DESC");
        $kd_pg = substr($kd_pegawai_temporary[0]->kd_pegawai, -3);
        $incremented = str_pad((int)$kd_pg + 1, 3, '0', STR_PAD_LEFT);
        $kd_pegawai = 'PAA' . $incremented;


        return view('pegawai', ['jabatan' => $jabatan, 'kd_pegawai' => $kd_pegawai]);
    }

    public function getDataPegawai(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';

        // indices must match the visible data columns IN ORDER (exclude action col);
        // qualify with table name to avoid ambiguity across the join:
        $columnsMap = [
            0 => 'm_pegawai.kd_pegawai',
            1 => 'm_pegawai.nama',
            2 => 'm_pegawai.keterangan',
            3 => 'm_pegawai.[status]',
            4 => 'm_jabatan.nama',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'm_pegawai.kd_pegawai';

        if ($length <= 0) { $length = 10; }

        // Build the JOIN once so counts + data stay consistent
        $from = "FROM m_pegawai INNER JOIN m_jabatan ON m_pegawai.kd_jabatan = m_jabatan.kd_jabatan";

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(m_pegawai.kd_pegawai LIKE ? OR m_pegawai.nama LIKE ? OR m_pegawai.keterangan LIKE ? OR m_jabatan.nama LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        // recordsTotal = COUNT of the base table only (no join needed, no where)
        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_pegawai")[0]->c;
        // recordsFiltered = COUNT with the join + where
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    m_pegawai.kd_pegawai AS kd_pegawai,
                    m_pegawai.nama AS pegawai,
                    m_pegawai.keterangan AS keterangan,
                    m_pegawai.[status] AS status_pegawai,
                    m_pegawai.kd_jabatan AS kd_jabatan,
                    m_jabatan.nama AS jabatan
                $from
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputPegawai(Request $request)
    {
        $kd_pegawai = $request->kd_pegawai;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $kd_jabatan = $request->kd_jabatan;
        $status = $request->status;

        DB::insert("INSERT INTO m_pegawai
                    (kd_pegawai, nama, keterangan, kd_jabatan, [status])
                    VALUES (?, ?, ?, ?, ?)", [$kd_pegawai, $nama, $keterangan, $kd_jabatan, $status]);
        return redirect()->route('index.master.pegawai');
    }

    public function getJabatanPegawai()
    {
        $jabatan = DB::select("SELECT 
                                kd_jabatan, 
                                nama 
                            FROM m_jabatan WHERE [status] = 1");
        return response()->json(['jabatan' => $jabatan]);
    }

    public function editPegawai(Request $request)
    {
        $kd_pegawai = $request->edit_kd_pegawai;
        $nama = $request->edit_nama_pegawai;
        $keterangan = $request->edit_keterangan_pegawai;
        $status = $request->edit_status_pegawai;
        $kd_jabatan = $request->edit_kdJabatan_pegawai;

        // print_r($request->edit_kdJabatan_pegawai);

        DB::update("UPDATE m_pegawai SET nama=?, keterangan=?, [status]=?, kd_jabatan=? WHERE kd_pegawai=?", [$nama, $keterangan, $status, $kd_jabatan, $kd_pegawai]);
        return redirect()->route('index.master.pegawai');
    }

    public function hapusPegawai(Request $request)
    {
        $kd_pegawai = $request->hapus_kd_pegawai;
        DB::delete("DELETE FROM m_pegawai WHERE kd_pegawai=?", [$kd_pegawai]);
        return redirect()->route('index.master.pegawai');
    }
}

