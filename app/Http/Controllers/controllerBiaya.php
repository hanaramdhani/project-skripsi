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
        $data = DB::select("SELECT
                                kd_biaya,
                                m_biaya.nama AS biaya,
                                m_biaya.keterangan AS keterangan,
                                m_biaya.[status] AS [status],
                                m_akun.kd_akun,
                                m_akun.nama AS akun
                            FROM m_biaya
                            INNER JOIN m_akun ON m_biaya.kd_akun = m_akun.kd_akun");

        $kd_biaya = $this->nextKdBiaya();

        $akun = DB::select("SELECT
                                kd_akun,
                                nama AS akun
                            FROM m_akun WHERE [status]=1");

        return view('biaya', ['data' => $data, 'kd_biaya' => $kd_biaya, 'akun' => $akun]);
    }

    public function inputBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kd_biaya'   => ['required', 'string', 'max:20'],
            'nama'       => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status'     => ['required', 'boolean'],
            'kd_akun'    => ['required', 'string', 'exists:m_akun,kd_akun'],
        ]);

        DB::insert(
            "INSERT INTO m_biaya (kd_biaya, kd_akun, nama, keterangan, [status])
             VALUES (?, ?, ?, ?, ?)",
            [
                $validated['kd_biaya'],
                $validated['kd_akun'],
                $validated['nama'],
                $validated['keterangan'] ?? '',
                $validated['status'],
            ]
        );

        return redirect()->route('index.master.biaya');
    }

    public function editGetAkun(): JsonResponse
    {
        $akun = DB::select("SELECT kd_akun, nama FROM m_akun WHERE [status] = 1");

        return response()->json(['akun' => $akun]);
    }

    public function editBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'edit_kd_biaya'        => ['required', 'string', 'exists:m_biaya,kd_biaya'],
            'edit_nama_biaya'      => ['required', 'string', 'max:255'],
            'edit_keterangan_biaya' => ['nullable', 'string', 'max:1000'],
            'edit_status_biaya'    => ['required', 'boolean'],
            'edit_kd_akun'         => ['required', 'string', 'exists:m_akun,kd_akun'],
        ]);

        DB::update(
            "UPDATE m_biaya
             SET kd_akun = ?, nama = ?, keterangan = ?, [status] = ?
             WHERE kd_biaya = ?",
            [
                $validated['edit_kd_akun'],
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
