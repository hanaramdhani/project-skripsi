<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBarangSatuan extends Controller
{
    public function viewMasterBarangSatuan()
    {
        $barang = DB::select("SELECT 
                                    kd_barang, 
                                    nama AS barang
                                FROM m_barang WHERE [status] = 1");
        $satuan = DB::select("SELECT 
                                    kd_satuan, 
                                    nama AS satuan
                                FROM m_satuan WHERE [status] = 1");
        return view('BarangSatuan', ['barang' => $barang, 'satuan' => $satuan]);
    }

    public function getDataBarangSatuan(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'm_barang.nama',
            1 => 'm_satuan.nama',
            2 => 'm_barang_satuan.harga_jual',
            3 => 'm_barang_satuan.keterangan',
            4 => 'm_barang_satuan.[status]',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'm_barang.nama';
        if ($length <= 0) { $length = 10; }

        $from = "FROM m_barang_satuan
                 INNER JOIN m_barang ON m_barang_satuan.kd_barang = m_barang.kd_barang
                 INNER JOIN m_satuan ON m_barang_satuan.kd_satuan = m_satuan.kd_satuan";

        $where = []; $bindings = [];
        if (!empty($search)) {
            $where[] = "(m_barang.nama LIKE ? OR m_satuan.nama LIKE ? OR m_barang_satuan.keterangan LIKE ?)";
            $bindings[] = "%$search%"; $bindings[] = "%$search%"; $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_barang_satuan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    m_barang.kd_barang AS kd_barang,
                    m_barang.nama AS barang,
                    m_satuan.kd_satuan AS kd_satuan,
                    m_satuan.nama AS satuan,
                    m_barang_satuan.harga_jual AS harga_jual,
                    m_barang_satuan.keterangan AS keterangan,
                    m_barang_satuan.[status] AS [status]
                $from $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw, 'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered, 'data' => $data,
        ]);
    }

    public function inputBarangSatuan(Request $request)
    {
        $kd_barang = $request->kd_barang;
        $kd_satuan = $request->kd_satuan;
        $harga_jual = $request->harga_jual;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_barang_satuan
                    (kd_barang, kd_satuan, harga_jual, keterangan, [status])
                    VALUES (?, ?, ?, ?, ?)", [$kd_barang, $kd_satuan, $harga_jual, $keterangan, $status]);
        return redirect()->route('index.master.barang.satuan');
    }

    public function getBarangSatuanEdit()
    {
        $barang = DB::select("SELECT 
                                kd_barang, 
                                nama 
                            FROM m_barang");
        $satuan = DB::select("SELECT 
                                kd_satuan, 
                                nama 
                            FROM m_satuan");
        return response()->json(['barang' => $barang, 'satuan' => $satuan]);
    }

    public function editBarangSatuan(Request $request)
    {
        $kd_barang = $request->edit_kd_barang;
        $kd_satuan = $request->edit_kd_satuan;
        $harga_jual = $request->edit_harga_jual;
        $keterangan = $request->edit_keterangan;
        $status = $request->edit_status;
        // print_r("UPDATE m_barang_satuan SET harga_jual='$harga_jual', keterangan='$keterangan', [status]='$status' WHERE kd_barang='$kd_barang' AND kd_satuan='$kd_satuan'");

        DB::update("UPDATE m_barang_satuan SET harga_jual=?, keterangan=?, [status]=? WHERE kd_barang=? AND kd_satuan=?", [$harga_jual, $keterangan, $status, $kd_barang, $kd_satuan]);
        return redirect()->route('index.master.barang.satuan');
    }

    public function hapusBarangSatuan(Request $request)
    {
        $kd_barang = $request->hapus_kd_barang;
        $kd_satuan = $request->hapus_kd_satuan;
        DB::delete("DELETE FROM m_barang_satuan WHERE kd_barang=? AND kd_satuan=?", [$kd_barang, $kd_satuan]);
        return redirect()->route('index.master.barang.satuan');
    }
}

