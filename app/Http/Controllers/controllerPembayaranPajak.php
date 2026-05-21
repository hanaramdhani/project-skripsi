<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controllerPembayaranPajak extends Controller
{
    /**
     * Generate kode NTPN dengan format: PPH + YYMMDD + 3 digit increment.
     * Contoh: PPH260519001 (tanggal 19 Mei 2026, urutan ke-1 pada hari itu).
     * 3 digit terakhir reset/increment sesuai tanggal berjalan (hari ini).
     */
    private function generateNtpn(): string
    {
        $prefix = 'PPH' . date('ymd'); // PPH + YYMMDD

        $row = DB::select(
            "SELECT TOP 1 ntpn FROM t_pembayaran_pajak
             WHERE ntpn LIKE ? ORDER BY ntpn DESC",
            [$prefix . '%']
        );

        if (!empty($row)) {
            $lastSeq = (int) substr($row[0]->ntpn, -3);
            $seq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $seq = '001';
        }

        return $prefix . $seq;
    }

    public function viewPembayaranPajak()
    {
        $data = DB::select("SELECT
                                id,
                                CONVERT(varchar(10), tanggal, 23)  AS tanggal_iso,
                                CONVERT(varchar(10), tanggal, 105) AS tanggal_tampil,
                                masa_pajak,
                                jenis_pajak,
                                nominal,
                                ntpn
                            FROM t_pembayaran_pajak
                            ORDER BY id DESC");

        $ntpn = $this->generateNtpn();

        return view('PembayaranPajak', ['data' => $data, 'ntpn' => $ntpn]);
    }

    public function inputPembayaranPajak(Request $request)
    {
        $tanggal     = $request->tanggal;
        $masa_pajak  = $request->masa_pajak;
        $jenis_pajak = $request->jenis_pajak;
        $nominal     = (float) ($request->nominal ?? 0);
        // NTPN di-generate ulang di server agar tidak duplikat walau form basi.
        $ntpn        = $this->generateNtpn();

        DB::insert("INSERT INTO t_pembayaran_pajak
                    (tanggal, masa_pajak, jenis_pajak, nominal, ntpn)
                    VALUES (?, ?, ?, ?, ?)",
                    [$tanggal, $masa_pajak, $jenis_pajak, $nominal, $ntpn]);

        return redirect()->route('index.pembayaran.pajak');
    }

    public function editPembayaranPajak(Request $request)
    {
        $id          = $request->edit_id;
        $tanggal     = $request->edit_tanggal;
        $masa_pajak  = $request->edit_masa_pajak;
        $jenis_pajak = $request->edit_jenis_pajak;
        $nominal     = (float) ($request->edit_nominal ?? 0);

        // NTPN tidak diubah saat edit (kode auto, read only).
        DB::update("UPDATE t_pembayaran_pajak
                    SET tanggal = ?, masa_pajak = ?, jenis_pajak = ?, nominal = ?
                    WHERE id = ?",
                    [$tanggal, $masa_pajak, $jenis_pajak, $nominal, $id]);

        return redirect()->route('index.pembayaran.pajak');
    }

    public function hapusPembayaranPajak(Request $request)
    {
        $id = $request->hapus_id;
        DB::delete("DELETE FROM t_pembayaran_pajak WHERE id = ?", [$id]);
        return redirect()->route('index.pembayaran.pajak');
    }

    /**
     * API: ambil nominal hutang pajak dari t_hutang_pajak berdasarkan
     * periode (year+month yang dipilih di front end) dan jenis pajak.
     *
     * Filter pada kolom tgl_pajak memakai rentang [awal bulan, awal bulan berikutnya)
     * agar tetap akurat walau tgl_pajak bertipe DATETIME.
     * Jika ada >1 baris pada periode tsb, ambil 1 baris terbaru (tgl_pajak DESC).
     *
     * Request: periode = "YYYY-MM-01", jenis_pajak = string
     * Response JSON: { found: bool, nominal: float }
     */
    public function cekHutangPajak(Request $request)
    {
        $periode     = (string) $request->query('periode', '');
        $jenis_pajak = (string) $request->query('jenis_pajak', '');

        // Validasi format periode wajib YYYY-MM-01.
        if (!preg_match('/^\d{4}-\d{2}-01$/', $periode)) {
            return response()->json(['found' => false, 'nominal' => 0]);
        }

        $row = DB::select(
            "SELECT TOP 1 nominal
             FROM t_hutang_pajak
             WHERE tgl_pajak >= ?
               AND tgl_pajak < DATEADD(MONTH, 1, ?)
               AND jenis_pajak = ?
             ORDER BY tgl_pajak DESC",
            [$periode, $periode, $jenis_pajak]
        );

        if (empty($row)) {
            return response()->json(['found' => false, 'nominal' => 0]);
        }

        return response()->json([
            'found'   => true,
            'nominal' => (float) $row[0]->nominal,
        ]);
    }
}
