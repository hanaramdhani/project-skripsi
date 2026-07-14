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

        $akun = DB::select("SELECT 
                                kd_akun, 
                                nama AS akun
                            FROM m_akun WHERE [status]=1");
        return view('pendapatan', ['kd_pendapatan' => $kd_pendapatan, 'akun' => $akun]);
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
            0 => 'm_pendapatan.kd_pendapatan',
            1 => 'm_pendapatan.nama',
            2 => 'm_pendapatan.[status]',
            3 => 'm_pendapatan.keterangan',
            4 => 'm_akun.nama',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'm_pendapatan.kd_pendapatan';
        if ($length <= 0) { $length = 10; }

        $from = "FROM m_pendapatan INNER JOIN m_akun ON m_pendapatan.kd_akun = m_akun.kd_akun";

        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(m_pendapatan.kd_pendapatan LIKE ? OR m_pendapatan.nama LIKE ? OR m_pendapatan.keterangan LIKE ? OR m_akun.nama LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_pendapatan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    m_pendapatan.kd_pendapatan AS kd_pendapatan,
                    m_pendapatan.nama AS pendapatan,
                    m_pendapatan.[status] AS [status],
                    m_pendapatan.keterangan AS keterangan,
                    m_akun.kd_akun AS kd_akun,
                    m_akun.nama AS akun
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
        $kd_akun = $request->kd_akun;

        DB::insert("INSERT INTO m_pendapatan
                    (kd_pendapatan, kd_akun, nama, keterangan, [status])
                    VALUES (?, ?, ?, ?, ?)", [$kd_pendapatan, $kd_akun, $nama, $keterangan, $status]);
        return redirect()->route('index.master.pendapatan');
    }

    public function editGetAkun()
    {
        $akun = DB::select("SELECT kd_akun, nama FROM m_akun WHERE [status] = 1");
        return response()->json(['akun' => $akun]);
    }

    public function editPendapatan(Request $request)
    {
        $kd_pendapatan = $request->edit_kd_pendapatan;
        $nama = $request->edit_nama_pendapatan;
        $keterangan = $request->edit_keterangan_pendapatan;
        $status = $request->edit_status_pendapatan;
        $kd_akun = $request->edit_kd_akun;

        DB::update("UPDATE m_pendapatan SET kd_akun=?, nama=?, keterangan=?, [status]=? WHERE kd_pendapatan=?", [$kd_akun, $nama, $keterangan, $status, $kd_pendapatan]);
        return redirect()->route('index.master.pendapatan');
    }

    public function hapusPendapatan(Request $request)
    {
        $kd_pendapatan = $request->hapus_kd_pendapatan;
        DB::delete("DELETE FROM m_pendapatan WHERE kd_pendapatan=?", [$kd_pendapatan]);
        return redirect()->route('index.master.pendapatan');
    }
}

