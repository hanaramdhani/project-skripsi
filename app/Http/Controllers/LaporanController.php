<?php

namespace App\Http\Controllers;

use App\Support\ExcelXml;
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

    // =====================================================================
    // Helper: tanggal default (tanggal jurnal terakhir)
    // =====================================================================
    private function defaultTgl()
    {
        $latest = DB::select("SELECT MAX(tgl_jurnal) AS tgl FROM jurnal_umum");
        return $latest[0]->tgl ?? date('Y-m-d');
    }

    private function resolvePeriode(Request $req)
    {
        $latest = $this->defaultTgl();
        return [
            $req->tgl_awal  ?? $latest,
            $req->tgl_akhir ?? $latest,
        ];
    }

    // =====================================================================
    // Query masing-masing laporan (dipakai ulang oleh view & export)
    // =====================================================================

    private function dataJurnalUmum($tgl_awal, $tgl_akhir)
    {
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

        return [
            'data'         => $data,
            'total_debit'  => $total_debit,
            'total_kredit' => $total_kredit,
        ];
    }

    private function dataLabaRugi($tgl_awal, $tgl_akhir)
    {
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

            if (stripos($kategori, 'pendapatan') !== false) {
                $total_pendapatan += (float) $r->nilai;
            } else {
                $total_beban += (float) $r->nilai;
            }
        }
        $laba_bersih = $total_pendapatan - $total_beban;

        return [
            'grouped'          => $grouped,
            'total_pendapatan' => $total_pendapatan,
            'total_beban'      => $total_beban,
            'laba_bersih'      => $laba_bersih,
        ];
    }

    private function dataNeraca($tgl_awal, $tgl_akhir)
    {
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

        return [
            'grouped'      => $grouped,
            'grand_debit'  => $grand_debit,
            'grand_kredit' => $grand_kredit,
            'grand_saldo'  => $grand_saldo,
        ];
    }

    // =====================================================================
    // Halaman (view)
    // =====================================================================

    public function viewJurnalUmum(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataJurnalUmum($tgl_awal, $tgl_akhir);

        return view('JurnalUmum', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanLabaRugi(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataLabaRugi($tgl_awal, $tgl_akhir);

        return view('LaporanLabaRugiPage', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanNeraca(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);
        $d = $this->dataNeraca($tgl_awal, $tgl_akhir);

        return view('LaporanNeraca', array_merge($d, [
            'tgl_awal'  => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
        ]));
    }

    public function viewLaporanArusKas(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);

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

    // =====================================================================
    // Export Excel (multi-laporan dalam 1 file, 1 sheet per laporan)
    // =====================================================================

    public function export(Request $req)
    {
        [$tgl_awal, $tgl_akhir] = $this->resolvePeriode($req);

        // Laporan yang dipilih (checkbox). Default: jurnal_umum bila kosong.
        $reports = (array) $req->input('reports', []);
        $reports = array_values(array_filter($reports, function ($r) {
            return in_array($r, ['jurnal_umum', 'laba_rugi', 'neraca'], true);
        }));
        if (empty($reports)) {
            $reports = ['jurnal_umum'];
        }

        $xls = new ExcelXml();

        foreach ($reports as $r) {
            if ($r === 'jurnal_umum') {
                $xls->addSheet('Jurnal Umum', $this->sheetJurnalUmum($tgl_awal, $tgl_akhir));
            } elseif ($r === 'laba_rugi') {
                $xls->addSheet('Laba Rugi', $this->sheetLabaRugi($tgl_awal, $tgl_akhir));
            } elseif ($r === 'neraca') {
                $xls->addSheet('Neraca', $this->sheetNeraca($tgl_awal, $tgl_akhir));
            }
        }

        $filename = 'Laporan_' . str_replace('-', '', $tgl_awal) . '_' . str_replace('-', '', $tgl_akhir) . '.xls';

        return $xls->download($filename);
    }

    // =====================================================================
    // Generate / Refresh Jurnal Umum
    // Memanggil SP sp_Generate_Jurnal_Umum dari 2 hari lalu s/d sekarang.
    // =====================================================================
    public function refreshJurnal(Request $req)
    {
        $start = date('Y-m-d H:i:s', strtotime('-2 days'));
        $end   = date('Y-m-d H:i:s');

        try {
            DB::statement("EXEC sp_Generate_Jurnal_Umum ?, ?", [$start, $end]);
            return back()->with('flash', [
                'type' => 'success',
                'text' => "Jurnal umum berhasil di-generate untuk periode $start s/d $end.",
            ]);
        } catch (\Throwable $e) {
            return back()->with('flash', [
                'type' => 'error',
                'text' => 'Gagal generate jurnal: ' . $e->getMessage(),
            ]);
        }
    }

    private function periodeLabel($tgl_awal, $tgl_akhir)
    {
        $fmt = function ($t) {
            $ts = strtotime($t);
            return $ts ? date('d/m/Y', $ts) : $t;
        };
        return 'Periode: ' . $fmt($tgl_awal) . ' s/d ' . $fmt($tgl_akhir);
    }

    private function sheetJurnalUmum($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataJurnalUmum($tgl_awal, $tgl_akhir);
        $rows = [];

        $rows[] = ExcelXml::row([ExcelXml::text('JURNAL UMUM', 'title', 6)], 22);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 6)];
        $rows[] = []; // baris kosong

        $rows[] = ExcelXml::row([
            ExcelXml::text('NO', 'header'),
            ExcelXml::text('TANGGAL', 'header'),
            ExcelXml::text('NO. BUKTI', 'header'),
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('SUMBER', 'header'),
            ExcelXml::text('DEBIT', 'header'),
            ExcelXml::text('KREDIT', 'header'),
        ], 30);

        $no = 0;
        foreach ($d['data'] as $row) {
            $no++;
            $ts = strtotime($row->tgl_jurnal);
            $rows[] = [
                ExcelXml::text($no, 'cellCenter'),
                ExcelXml::text($ts ? date('d/m/Y', $ts) : $row->tgl_jurnal, 'cellCenter'),
                ExcelXml::text($row->no_bukti, 'cellCenter'),
                ExcelXml::text($row->coa_kode, 'cellCenter'),
                ExcelXml::text($row->sumber, 'cell'),
                ExcelXml::number($row->debit, 'money'),
                ExcelXml::number($row->kredit, 'money'),
            ];
        }

        if ($no === 0) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 6)];
        }

        // 'TOTAL' di-merge menutup kolom NO..SUMBER (A:E), lalu nilai di DEBIT (F) & KREDIT (G).
        $rows[] = [
            ExcelXml::text('TOTAL', 'totalLabelDark', 4),
            ExcelXml::number($d['total_debit'], 'moneyTotal'),
            ExcelXml::number($d['total_kredit'], 'moneyTotal'),
        ];

        return $rows;
    }

    private function sheetLabaRugi($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataLabaRugi($tgl_awal, $tgl_akhir);
        $rows = [];

        $rows[] = ExcelXml::row([ExcelXml::text('LAPORAN LABA RUGI', 'title', 2)], 22);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 2)];
        $rows[] = [];

        $rows[] = ExcelXml::row([
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('NAMA AKUN', 'header'),
            ExcelXml::text('NILAI', 'header'),
        ], 30);

        if (empty($d['grouped'])) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 2)];
        } else {
            foreach ($d['grouped'] as $kategori => $group) {
                $rows[] = [ExcelXml::text(strtoupper($kategori), 'group', 2)];
                foreach ($group['items'] as $item) {
                    $rows[] = [
                        ExcelXml::text($item->coa_kode, 'cellCenter'),
                        ExcelXml::text($item->coa_nama, 'cell'),
                        ExcelXml::number($item->nilai, 'money'),
                    ];
                }
                $rows[] = ExcelXml::row([
                    ExcelXml::text('Subtotal ' . $kategori, 'totalLabel', 1),
                    ExcelXml::number($group['subtotal'], 'moneyBold'),
                ], 32);
            }
        }

        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL PENDAPATAN', 'totalLabel', 1),
            ExcelXml::number($d['total_pendapatan'], 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL BEBAN', 'totalLabel', 1),
            ExcelXml::number($d['total_beban'], 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text($d['laba_bersih'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH', 'totalLabelDark', 1),
            ExcelXml::number($d['laba_bersih'], 'moneyTotal'),
        ], 32);

        return $rows;
    }

    private function sheetNeraca($tgl_awal, $tgl_akhir)
    {
        $d = $this->dataNeraca($tgl_awal, $tgl_akhir);
        $grouped = $d['grouped'];
        $rows = [];

        $tipeLabels = [
            'ASSET'     => 'ASET',
            'LIABILITY' => 'LIABILITAS (KEWAJIBAN)',
            'EQUITY'    => 'EKUITAS (MODAL)',
        ];

        $totalAset       = $grouped['ASSET']['saldo']     ?? 0;
        $totalLiabilitas = $grouped['LIABILITY']['saldo'] ?? 0;
        $totalEkuitas    = $grouped['EQUITY']['saldo']    ?? 0;
        $totalPasiva     = $totalLiabilitas + $totalEkuitas;
        $isBalance       = abs($totalAset - $totalPasiva) < 0.01;

        $rows[] = ExcelXml::row([ExcelXml::text('LAPORAN NERACA (BALANCE SHEET)', 'title', 2)], 40);
        $rows[] = [ExcelXml::text($this->periodeLabel($tgl_awal, $tgl_akhir), 'subtitle', 2)];
        $rows[] = [];

        $rows[] = ExcelXml::row([
            ExcelXml::text('KODE COA', 'header'),
            ExcelXml::text('NAMA AKUN', 'header'),
            ExcelXml::text('JUMLAH', 'header'),
        ], 30);

        $adaData = false;
        $blok = function ($key, $label, $total) use (&$rows, $grouped) {
            if (empty($grouped[$key])) {
                return false;
            }
            $rows[] = [ExcelXml::text($label, 'group', 2)];
            foreach ($grouped[$key]['items'] as $item) {
                $rows[] = [
                    ExcelXml::text($item->coa_kode, 'cellCenter'),
                    ExcelXml::text($item->coa_nama, 'cell'),
                    ExcelXml::number($item->saldo, 'money'),
                ];
            }
            $rows[] = ExcelXml::row([
                ExcelXml::text('TOTAL ' . $label, 'totalLabel', 1),
                ExcelXml::number($total, 'moneyBold'),
            ], 32);
            return true;
        };

        if ($blok('ASSET', $tipeLabels['ASSET'], $totalAset)) {
            $adaData = true;
        }
        if ($blok('LIABILITY', $tipeLabels['LIABILITY'], $totalLiabilitas)) {
            $adaData = true;
        }
        if ($blok('EQUITY', $tipeLabels['EQUITY'], $totalEkuitas)) {
            $adaData = true;
        }

        if (!$adaData) {
            $rows[] = [ExcelXml::text('Tidak ada data pada periode ini.', 'cellCenter', 2)];
        }

        $rows[] = ExcelXml::row([
            ExcelXml::text('TOTAL LIABILITAS + EKUITAS', 'totalLabel', 1),
            ExcelXml::number($totalPasiva, 'moneyBold'),
        ], 32);
        $rows[] = ExcelXml::row([
            ExcelXml::text($isBalance ? 'BALANCE SHEET (Seimbang)' : 'BALANCE SHEET (Tidak Seimbang)', 'totalLabelDark', 1),
            ExcelXml::number($totalAset, 'moneyTotal'),
        ], 32);

        return $rows;
    }
}
