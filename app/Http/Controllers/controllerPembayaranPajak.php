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
        $ntpn = $this->generateNtpn();

        return view('PembayaranPajak', ['ntpn' => $ntpn]);
    }

    /**
     * Server-side DataTables endpoint untuk tabel Pembayaran Pajak.
     *
     * Catatan: kolom "periode" TIDAK ada di tabel t_pembayaran_pajak
     * (lihat database/sql/create_t_pembayaran_pajak.sql). Sesuai perilaku
     * viewPembayaranPajak yang lama, kolom PERIODE selalu tampil "-" dan
     * input periode pada modal edit dibiarkan kosong (periode_iso = '').
     */
    public function getDataPembayaranPajak(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value', '');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        // Whitelist ORDER BY. Index 2 (PERIODE) & 6 (aksi) tidak dapat diurutkan
        // (tidak ada kolom fisik) -> fallback ke id.
        $columnsMap = [
            0 => 'tanggal',
            1 => 'masa_pajak',
            3 => 'jenis_pajak',
            4 => 'nominal',
            5 => 'ntpn',
        ];
        $orderColumn = $columnsMap[$orderColumnIndex] ?? 'id';
        if ($length <= 0) { $length = 10; }

        $where = [];
        $bindings = [];
        if (!empty($search)) {
            $where[] = "(masa_pajak LIKE ? OR jenis_pajak LIKE ? OR ntpn LIKE ?)";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
            $bindings[] = "%$search%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $recordsTotal    = DB::select("SELECT COUNT(*) AS c FROM t_pembayaran_pajak")[0]->c;
        $recordsFiltered = DB::select("SELECT COUNT(*) AS c FROM t_pembayaran_pajak $whereSql", $bindings)[0]->c;

        $sql = "SELECT
                    id,
                    CONVERT(varchar(10), tanggal, 23)  AS tanggal_iso,     -- YYYY-MM-DD (input date modal edit)
                    CONVERT(varchar(10), tanggal, 105) AS tanggal_tampil,  -- DD-MM-YYYY (kolom tampil)
                    masa_pajak,                                            -- YYYY-MM (dipakai tampil & input month edit)
                    jenis_pajak,
                    nominal,
                    ntpn
                FROM t_pembayaran_pajak
                $whereSql
                ORDER BY $orderColumn $orderDir
                OFFSET $start ROWS FETCH NEXT $length ROWS ONLY";
        $data = DB::select($sql, $bindings);

        // Kolom PERIODE tidak ada di tabel -> tampil "-" dan periode_iso kosong,
        // meniru persis perilaku viewPembayaranPajak yang lama.
        foreach ($data as $row) {
            $row->periode_tampil = '-';
            $row->periode_iso    = '';
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
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
