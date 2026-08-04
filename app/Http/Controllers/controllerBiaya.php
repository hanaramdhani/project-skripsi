<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class controllerBiaya extends Controller
{
    public function viewMasterBiaya(): View
    {
        $kd_biaya = $this->nextKdBiaya();

        return view('biaya', ['kd_biaya' => $kd_biaya]);
    }

    public function getDataBiaya(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columnsMap = [
            0 => 'kd_biaya',
            1 => 'nama',
            2 => '[status]',
            3 => 'keterangan',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'kd_biaya';
        if ($length <= 0) {
            $length = 10;
        }

        $from = "FROM m_biaya";

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(kd_biaya LIKE ? OR nama LIKE ? OR keterangan LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM m_biaya")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c $from $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    kd_biaya,
                    nama AS biaya,
                    keterangan,
                    [status]
                $from $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function inputBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kd_biaya'   => ['required', 'string', 'max:20'],
            'nama'       => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status'     => ['required', 'boolean'],
        ]);

        DB::insert(
            "INSERT INTO m_biaya (kd_biaya, nama, keterangan, [status])
             VALUES (?, ?, ?, ?)",
            [
                $validated['kd_biaya'],
                $validated['nama'],
                $validated['keterangan'] ?? '',
                $validated['status'],
            ]
        );

        return redirect()->route('index.master.biaya');
    }

    public function editBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'edit_kd_biaya'        => ['required', 'string', 'exists:m_biaya,kd_biaya'],
            'edit_nama_biaya'      => ['required', 'string', 'max:255'],
            'edit_keterangan_biaya' => ['nullable', 'string', 'max:1000'],
            'edit_status_biaya'    => ['required', 'boolean'],
        ]);

        DB::update(
            "UPDATE m_biaya
             SET nama = ?, keterangan = ?, [status] = ?
             WHERE kd_biaya = ?",
            [
                $validated['edit_nama_biaya'],
                $validated['edit_keterangan_biaya'] ?? '',
                $validated['edit_status_biaya'],
                $validated['edit_kd_biaya'],
            ]
        );

        return redirect()->route('index.master.biaya');
    }

    public function hapusBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hapus_kd_biaya' => ['required', 'string', 'exists:m_biaya,kd_biaya'],
        ]);

        DB::delete("DELETE FROM m_biaya WHERE kd_biaya = ?", [$validated['hapus_kd_biaya']]);

        return redirect()->route('index.master.biaya');
    }

    /**
     * Generate the next sequential kd_biaya (e.g. BAA001 -> BAA002).
     * Returns the seed value when the table is empty.
     */
    private function nextKdBiaya(): string
    {
        $latest = DB::select("SELECT TOP 1 kd_biaya FROM m_biaya ORDER BY kd_biaya DESC");

        if (empty($latest)) {
            return 'BAA001';
        }

        $lastNumber = (int) substr($latest[0]->kd_biaya, -3);
        $incremented = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);

        return 'BAA' . $incremented;
    }
}
