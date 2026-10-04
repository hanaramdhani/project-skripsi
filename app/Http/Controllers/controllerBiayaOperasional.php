<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerBiayaOperasional extends Controller
{
    /** No transaksi: BO + YYYYMMDD + 4 digit urut harian. Contoh: BO202610040001 */
    private function generateNoTransaksi(): string
    {
        $prefix = 'BO' . date('Ymd');

        $row = DB::select(
            "SELECT TOP 1 no_transaksi FROM t_biaya_operasional
             WHERE no_transaksi LIKE ? ORDER BY no_transaksi DESC",
            [$prefix . '%']
        );

        $seq = empty($row) ? 1 : ((int) substr($row[0]->no_transaksi, -4)) + 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function viewBiayaOperasional()
    {
        $biaya = DB::select("SELECT kd_biaya, nama FROM m_biaya WHERE [status] = 1 ORDER BY nama");

        return view('BiayaOperasional', [
            'no_transaksi' => $this->generateNoTransaksi(),
            'biaya'        => $biaya,
        ]);
    }

    public function getDataBiayaOperasional(Request $request)
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
            2 => 'b.nama',
            3 => 'CAST(t.nominal AS decimal(18,2))',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 't.tanggal';
        if ($length <= 0) { $length = 10; }

        $from = "FROM t_biaya_operasional t
                 LEFT JOIN m_biaya b  ON b.kd_biaya = t.kd_biaya";

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(t.no_transaksi LIKE ? OR b.nama LIKE ?)";
            array_push($bindings, "%$search%", "%$search%");
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM t_biaya_operasional")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    t.no_transaksi,
                    t.kd_biaya,  b.nama AS biaya,
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

    public function inputBiayaOperasional(Request $request)
    {
        $request->validate([
            'tanggal'   => ['required', 'date'],
            'kd_biaya'  => ['required', 'string'],
            'nominal'   => ['required', 'numeric', 'min:0'],
        ]);

        // No transaksi di-generate ulang di server agar tidak duplikat walau form basi.
        DB::insert("INSERT INTO t_biaya_operasional
                    (no_transaksi, kd_biaya, tanggal, nominal)
                    VALUES (?, ?, ?, ?)",
                    [$this->generateNoTransaksi(), $request->kd_biaya,
                     $request->tanggal, (string) (float) $request->nominal]);

        return redirect()->route('index.biaya.operasional');
    }

    public function editBiayaOperasional(Request $request)
    {
        $request->validate([
            'edit_no_transaksi' => ['required', 'string', 'exists:t_biaya_operasional,no_transaksi'],
            'edit_tanggal'      => ['required', 'date'],
            'edit_kd_biaya'     => ['required', 'string'],
            'edit_nominal'      => ['required', 'numeric', 'min:0'],
        ]);

        DB::update("UPDATE t_biaya_operasional
                    SET tanggal = ?, kd_biaya = ?, nominal = ?
                    WHERE no_transaksi = ?",
                    [$request->edit_tanggal, $request->edit_kd_biaya,
                     (string) (float) $request->edit_nominal, $request->edit_no_transaksi]);

        return redirect()->route('index.biaya.operasional');
    }

    public function hapusBiayaOperasional(Request $request)
    {
        $request->validate(['hapus_no_transaksi' => ['required', 'string']]);
        DB::delete("DELETE FROM t_biaya_operasional WHERE no_transaksi = ?", [$request->hapus_no_transaksi]);

        return redirect()->route('index.biaya.operasional');
    }
}
