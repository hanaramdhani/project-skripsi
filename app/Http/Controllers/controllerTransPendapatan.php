<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerTransPendapatan extends Controller
{
    /** No transaksi: PD + YYYYMMDD + 4 digit urut harian. Contoh: PD202610040001 */
    private function generateNoTransaksi(): string
    {
        $prefix = 'PD' . date('Ymd');

        $row = DB::select(
            "SELECT TOP 1 no_transaksi FROM t_pendapatan
             WHERE no_transaksi LIKE ? ORDER BY no_transaksi DESC",
            [$prefix . '%']
        );

        $seq = empty($row) ? 1 : ((int) substr($row[0]->no_transaksi, -4)) + 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function viewTransPendapatan()
    {
        $pendapatan = DB::select("SELECT kd_pendapatan, nama FROM m_pendapatan WHERE [status] = 1 ORDER BY nama");

        return view('TransPendapatan', [
            'no_transaksi' => $this->generateNoTransaksi(),
            'pendapatan'   => $pendapatan,
        ]);
    }

    public function getDataTransPendapatan(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $columnsMap = [
            0 => 't.tanggal',
            1 => 't.no_transaksi',
            2 => 'p.nama',
            3 => 'CAST(t.nominal AS decimal(18,2))',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 't.tanggal';
        if ($length <= 0) { $length = 10; }

        $from = "FROM t_pendapatan t
                 LEFT JOIN m_pendapatan p ON p.kd_pendapatan = t.kd_pendapatan";

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(t.no_transaksi LIKE ? OR p.nama LIKE ?)";
            array_push($bindings, "%$search%", "%$search%");
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM t_pendapatan")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    t.no_transaksi,
                    t.kd_pendapatan, p.nama AS pendapatan,
                    CONVERT(varchar(10), t.tanggal, 23)  AS tanggal_iso,
                    CONVERT(varchar(10), t.tanggal, 105) AS tanggal_tampil,
                    t.nominal
                $from $whereSql
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

    public function inputTransPendapatan(Request $request)
    {
        $request->validate([
            'tanggal'       => ['required', 'date'],
            'kd_pendapatan' => ['required', 'string'],
            'nominal'       => ['required', 'numeric', 'min:0'],
        ]);

        // No transaksi di-generate ulang di server agar tidak duplikat walau form basi.
        DB::insert("INSERT INTO t_pendapatan
                    (no_transaksi, kd_pendapatan, tanggal, nominal)
                    VALUES (?, ?, ?, ?)",
                    [$this->generateNoTransaksi(), $request->kd_pendapatan,
                     $request->tanggal, (string) (float) $request->nominal]);

        return redirect()->route('index.trans.pendapatan');
    }

    public function editTransPendapatan(Request $request)
    {
        $request->validate([
            'edit_no_transaksi'  => ['required', 'string', 'exists:t_pendapatan,no_transaksi'],
            'edit_tanggal'       => ['required', 'date'],
            'edit_kd_pendapatan' => ['required', 'string'],
            'edit_nominal'       => ['required', 'numeric', 'min:0'],
        ]);

        DB::update("UPDATE t_pendapatan
                    SET tanggal = ?, kd_pendapatan = ?, nominal = ?
                    WHERE no_transaksi = ?",
                    [$request->edit_tanggal, $request->edit_kd_pendapatan,
                     (string) (float) $request->edit_nominal, $request->edit_no_transaksi]);

        return redirect()->route('index.trans.pendapatan');
    }

    public function hapusTransPendapatan(Request $request)
    {
        $request->validate(['hapus_no_transaksi' => ['required', 'string']]);
        DB::delete("DELETE FROM t_pendapatan WHERE no_transaksi = ?", [$request->hapus_no_transaksi]);

        return redirect()->route('index.trans.pendapatan');
    }
}
