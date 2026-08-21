<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBarang extends Controller
{
    public function viewMasterBarang()
    {
        $kd_barang_temporary = DB::select("SELECT TOP 1 kd_barang FROM m_barang ORDER BY kd_barang DESC");
        $kd_br = substr($kd_barang_temporary[0]->kd_barang, -3);
        $incremented = str_pad((int)$kd_br + 1, 3, '0', STR_PAD_LEFT);
        $kd_barang = 'BAA' . $incremented;
        return view('Barang', ['kd_barang' => $kd_barang]);
    }

    public function getDataBarang(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_barang',
            1 => 'nama',
            2 => 'status',
            3 => 'tanggal_daftar',
            4 => 'keterangan',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_barang';

        if ($length <= 0) {
            $length = 10;
        }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_barang LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_barang")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM m_barang $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    kd_barang,
                    nama,
                    status,
                    CONCAT(DAY(tanggal_daftar), ' ',
                        CASE MONTH(tanggal_daftar)
                            WHEN 1 THEN 'Januari'
                            WHEN 2 THEN 'Februari'
                            WHEN 3 THEN 'Maret'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'Mei'
                            WHEN 6 THEN 'Juni'
                            WHEN 7 THEN 'Juli'
                            WHEN 8 THEN 'Agustus'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'Oktober'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'Desember'
                        END, ' ', YEAR(tanggal_daftar)
                    ) AS tanggal_daftar,
                    keterangan
                FROM m_barang
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

    public function inputBarang(Request $request)
    {
        $kd_barang = $request->kd_barang;
        $nama = $request->nama;
        $keterangan = $request->keterangan;
        $status = $request->status;

        DB::insert("INSERT INTO m_barang
                    (kd_barang, nama, keterangan, status, tanggal_daftar, date_add)
                    VALUES (?, ?, ?, ?, GETDATE(), GETDATE())", [$kd_barang, $nama, $keterangan, $status]);
        return redirect()->route('index.master.barang');
    }

    // Tambah barang + satuan (harga jual & harga beli) sekaligus, dipakai dari
    // modal "Tambah Barang Baru" di halaman Input Pembelian.
    public function inputBarangCepat(Request $request)
    {
        $request->validate([
            'kd_barang'  => 'required|string',
            'nama'       => 'required|string',
            'kd_satuan'  => 'required|string',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        $kd_barang  = $request->kd_barang;
        $nama       = $request->nama;
        $keterangan = $request->keterangan ?: '-';
        $kd_satuan  = $request->kd_satuan;
        $harga_jual = (float) $request->harga_jual;

        try {
            DB::transaction(function () use ($kd_barang, $nama, $keterangan, $kd_satuan, $harga_jual) {
                DB::insert("INSERT INTO m_barang
                            (kd_barang, nama, keterangan, status, tanggal_daftar, date_add)
                            VALUES (?, ?, ?, 1, GETDATE(), GETDATE())", [$kd_barang, $nama, $keterangan]);

                DB::insert("INSERT INTO m_barang_satuan
                            (kd_barang, kd_satuan, harga_jual, keterangan, [status])
                            VALUES (?, ?, ?, ?, 1)", [$kd_barang, $kd_satuan, $harga_jual, $keterangan]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan barang baru: ' . $e->getMessage()], 500);
        }

        $satuanRow  = DB::select("SELECT nama FROM m_satuan WHERE kd_satuan = ?", [$kd_satuan]);
        $satuanNama = !empty($satuanRow) ? $satuanRow[0]->nama : '';

        return response()->json([
            'success'    => true,
            'kd_barang'  => $kd_barang,
            'barang'     => $nama,
            'kd_satuan'  => $kd_satuan,
            'satuan'     => $satuanNama,
            'harga_jual' => $harga_jual,
            'harga_beli' => $harga_jual,
        ]);
    }

    public function editBarang(Request $request)
    {
        $kd_barang = $request->edit_kd_barang;
        $nama = $request->edit_nama_barang;
        $keterangan = $request->edit_keterangan_barang;
        $status = $request->edit_status_barang;

        DB::update("UPDATE m_barang SET nama=?, keterangan=?, status=? WHERE kd_barang=?", [$nama, $keterangan, $status, $kd_barang]);
        return redirect()->route('index.master.barang');
    }

    public function hapusBarang(Request $request)
    {
        $kd_barang = $request->hapus_kd_barang;
        DB::delete("DELETE FROM m_barang WHERE kd_barang=?", [$kd_barang]);
        return redirect()->route('index.master.barang');
    }
}

