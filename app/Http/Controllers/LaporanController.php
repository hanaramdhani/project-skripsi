<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class LaporanController extends Controller
{
    public function index()
    {
        # code...
    }
    public function getLaporanLabaRugi(Request $req)
    {
        $awal = $req->awal;
        $akhir = $req->akhir;
        $data = DB::select("SELECT * from r_laba_rugi_harian WHERE tanggal BETWEEN '2025-04-03' AND '2025-04-08'");
        // $data = DB::select("CALL GetLabaRugi('$awal','$akhir')");
        // return response()->json(['data' => $data]);
        return view('LaporanLabaRugi', ['data'=>$data]);
    }

    public function viewJurnalUmum(Request $req)
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        $latest_tgl = $latest[0]->tgl ?? date('Y-m-d');

        $tgl_awal  = $req->tgl_awal  ?? $latest_tgl;
        $tgl_akhir = $req->tgl_akhir ?? $latest_tgl;

        $data = DB::select("
            SELECT tgl_jurnal, no_bukti, coa_kode, debit, kredit, sumber
            FROM jurnal_umum
            WHERE tgl_jurnal BETWEEN ? AND ?
            ORDER BY tgl_jurnal ASC, no_bukti ASC
        ", [$tgl_awal, $tgl_akhir]);

        $total_debit = 0;
        $total_kredit = 0;
        foreach ($data as $row) {
            $total_debit  += (float) $row->debit;
            $total_kredit += (float) $row->kredit;
        }

        return view('JurnalUmum', [
            'data'         => $data,
            'tgl_awal'     => $tgl_awal,
            'tgl_akhir'    => $tgl_akhir,
            'total_debit'  => $total_debit,
            'total_kredit' => $total_kredit,
        ]);
    }

    public function viewLaporanLabaRugi(Request $req)
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        $latest_tgl = $latest[0]->tgl ?? date('Y-m-d');

        $tgl_awal  = $req->tgl_awal  ?? $latest_tgl;
        $tgl_akhir = $req->tgl_akhir ?? $latest_tgl;
        
        $rows = DB::select("SELECT * FROM dbo.fn_Laporan_LabaRugi(?, ?) ORDER BY coa_kode", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $total_pendapatan = 0;
        $total_beban = 0;
        foreach ($rows as $r) {
            $kategori = $r->kategori;
            if (!isset($grouped[$kategori])) {
                $grouped[$kategori] = ['items' => [], 'subtotal' => 0];
            }
            $grouped[$kategori]['items'][] = $r;
            $grouped[$kategori]['subtotal'] += (float) $r->nilai;

            // SP mengembalikan 3 kategori: 'Pendapatan', 'Harga Pokok Penjualan',
            // 'Biaya Operasional'. Hanya 'Pendapatan' yang revenue; HPP & biaya
            // operasional adalah beban (pengurang laba), bukan ditambahkan.
            if (stripos($kategori, 'pendapatan') !== false) {
                $total_pendapatan += (float) $r->nilai;
            } else {
                $total_beban += (float) $r->nilai;
            }
        }
        $laba_bersih = $total_pendapatan - $total_beban;

        return view('LaporanLabaRugiPage', [
            'grouped'          => $grouped,
            'total_pendapatan' => $total_pendapatan,
            'total_beban'      => $total_beban,
            'laba_bersih'      => $laba_bersih,
            'tgl_awal'         => $tgl_awal,
            'tgl_akhir'        => $tgl_akhir,
        ]);
    }

    public function viewLaporanNeraca(Request $req)
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        $latest_tgl = $latest[0]->tgl ?? date('Y-m-d');

        $tgl_awal  = $req->tgl_awal  ?? $latest_tgl;
        $tgl_akhir = $req->tgl_akhir ?? $latest_tgl;

        $rows = DB::select("SELECT * FROM dbo.fn_Neraca_Saldo(?, ?) ORDER BY coa_kode", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $grand_debit = 0;
        $grand_kredit = 0;
        $grand_saldo = 0;
        foreach ($rows as $r) {
            $tipe = $r->coa_tipe ?? '-';
            if (!isset($grouped[$tipe])) {
                $grouped[$tipe] = ['items' => [], 'debit' => 0, 'kredit' => 0, 'saldo' => 0];
            }
            $grouped[$tipe]['items'][]   = $r;
            $grouped[$tipe]['debit']    += (float) $r->total_debit;
            $grouped[$tipe]['kredit']   += (float) $r->total_kredit;
            $grouped[$tipe]['saldo']    += (float) $r->saldo;
            $grand_debit  += (float) $r->total_debit;
            $grand_kredit += (float) $r->total_kredit;
            $grand_saldo  += (float) $r->saldo;
        }

        return view('LaporanNeraca', [
            'grouped'      => $grouped,
            'grand_debit'  => $grand_debit,
            'grand_kredit' => $grand_kredit,
            'grand_saldo'  => $grand_saldo,
            'tgl_awal'     => $tgl_awal,
            'tgl_akhir'    => $tgl_akhir,
        ]);
    }

    public function viewLaporanArusKas(Request $req)
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        $latest_tgl = $latest[0]->tgl ?? date('Y-m-d');

        $tgl_awal  = $req->tgl_awal  ?? $latest_tgl;
        $tgl_akhir = $req->tgl_akhir ?? $latest_tgl;

        $rows = DB::select("EXEC sp_Arus_Kas ?, ?", [$tgl_awal, $tgl_akhir]);

        $grouped = [];
        $grand_total = 0;
        foreach ($rows as $r) {
            $parts = explode(' - ', $r->aktivitas, 2);
            $group = trim($parts[0]);
            $label = isset($parts[1]) ? trim($parts[1]) : $r->aktivitas;

            if (!isset($grouped[$group])) {
                $grouped[$group] = ['items' => [], 'subtotal' => 0];
            }
            $grouped[$group]['items'][]   = (object) ['label' => $label, 'total' => $r->total];
            $grouped[$group]['subtotal'] += (float) $r->total;
            $grand_total += (float) $r->total;
        }

        return view('LaporanArusKas', [
            'grouped'     => $grouped,
            'grand_total' => $grand_total,
            'tgl_awal'    => $tgl_awal,
            'tgl_akhir'   => $tgl_akhir,
        ]);
    }
}
