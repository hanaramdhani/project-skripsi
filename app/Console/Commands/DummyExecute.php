<?php

namespace App\Console\Commands;

use App\Support\DummyTransaksiGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Eksekusi file SQL hasil dummy:generate, lalu jalankan SP turunan (harga beli terakhir,
 * jurnal umum, hutang PPh final) dan tampilkan hasil validasi.
 * Contoh: php artisan dummy:execute database/dummy/output/dummy_transaksi_....sql
 */
class DummyExecute extends Command
{
    protected $signature = 'dummy:execute
                            {file : Path file SQL hasil dummy:generate}
                            {--no-sp : Jangan jalankan SP setelah insert}
                            {--force : Tanpa konfirmasi}';

    protected $description = 'Eksekusi file SQL data dummy + jalankan SP jurnal, harga beli terakhir & PPh final';

    public function handle()
    {
        $file = $this->argument('file');
        if (!is_file($file) && is_file(base_path($file))) {
            $file = base_path($file);
        }

        $db = config('database.connections.' . config('database.default'));
        if (!$this->option('force')
            && !$this->confirm("Insert data dummy dari $file ke database {$db['database']} @ {$db['host']}?")) {
            $this->warn('Dibatalkan.');
            return 1;
        }

        $generator = new DummyTransaksiGenerator(function ($pesan) {
            $this->line($pesan);
        });
        $periode = $generator->execute($file, !$this->option('no-sp'));

        $this->newLine();
        $this->info('Validasi periode ' . $periode['from'] . ' s/d ' . $periode['to']);
        $minus = DB::selectOne('SELECT COUNT(*) AS c FROM v_stok_barang WHERE stok < 0')->c;
        $harian = DB::selectOne("SELECT COUNT(*) AS hari, MIN(omzet) AS mn, AVG(omzet) AS av, MAX(omzet) AS mx,
                                        MIN(c) AS nota_min, MAX(c) AS nota_max
                                 FROM (SELECT CAST(p.tanggal AS DATE) AS d, COUNT(DISTINCT p.no_transaksi) AS c,
                                              SUM(d.harga_jual * d.qty) AS omzet
                                       FROM t_penjualan p JOIN t_penjualan_detail d ON d.no_transaksi = p.no_transaksi
                                       WHERE p.tanggal >= ? AND p.tanggal < DATEADD(DAY, 1, CAST(? AS DATE))
                                       GROUP BY CAST(p.tanggal AS DATE)) x", [$periode['from'], $periode['to']]);
        $rp = function ($v) {
            return number_format((float) $v, 0, ',', '.');
        };
        $this->table(['Cek', 'Hasil'], [
            ['Barang stok minus', $minus],
            ['Hari ada penjualan', $harian->hari],
            ['Omzet harian min / rata-rata / max', $rp($harian->mn) . ' / ' . $rp($harian->av) . ' / ' . $rp($harian->mx)],
            ['Nota per hari min / max', $harian->nota_min . ' / ' . $harian->nota_max],
        ]);

        return $minus > 0 ? 1 : 0;
    }
}
