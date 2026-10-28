<?php

namespace App\Console\Commands;

use App\Support\DummyTransaksiGenerator;
use Illuminate\Console\Command;

/**
 * Generate file SQL insert data dummy transaksi. Tidak menulis ke database.
 * Contoh: php artisan dummy:generate
 *         php artisan dummy:generate --from=2026-07-20 --to=2026-10-28 --seed=123
 */
class DummyGenerate extends Command
{
    protected $signature = 'dummy:generate
                            {--from= : Tanggal mulai Y-m-d (default: tanggal penjualan terakhir + 1)}
                            {--to= : Tanggal akhir Y-m-d (default: hari ini, dibuat 1 hari penuh)}
                            {--seed= : Seed random agar hasil bisa diulang persis}
                            {--sampai-jam-sekarang : Transaksi hari ini hanya s/d jam sekarang}
                            {--quiet-days : Jangan tampilkan ringkasan per hari}';

    protected $description = 'Generate file SQL data dummy penjualan, pembelian & biaya (lihat database/dummy/README.md)';

    public function handle()
    {
        $hasil = $this->generate();
        $this->line('Eksekusi: php artisan dummy:execute "' . $hasil['file'] . '"');

        return 0;
    }

    /** Generate file SQL sesuai opsi command & tampilkan ringkasannya. Dipakai juga oleh dummy:ingest. */
    protected function generate()
    {
        $tampilHarian = !$this->option('quiet-days');
        $generator = new DummyTransaksiGenerator(function ($pesan) use ($tampilHarian) {
            if ($tampilHarian || !preg_match('/^\d{4}-\d{2}-\d{2} /', $pesan)) {
                $this->line($pesan);
            }
        });

        $hasil = $generator->generate($this->option('from'), $this->option('to'), $this->option('seed'),
            null, (bool) $this->option('sampai-jam-sekarang'));

        $this->newLine();
        $this->table(['Tabel', 'Baris'], collect($hasil['total'])->map(function ($c, $t) {
            return [$t, number_format($c, 0, ',', '.')];
        })->values()->all());

        $this->table(['Bulan', 'Hari', 'Rata-rata omzet/hari', 'Deviasi vs acuan'], array_map(function ($b) {
            return [$b['bulan'], $b['hari'], number_format($b['rata_omzet'], 0, ',', '.'), sprintf('%+.1f%%', $b['deviasi_pct'])];
        }, $hasil['bulanan']));
        $dev = collect($hasil['ringkasan'])->reject(function ($r) {
            return $r['parsial'];
        })->pluck('deviasi_pct');
        if ($dev->isNotEmpty()) {
            $this->line(sprintf('Omzet harian vs acuan: min %+.1f%%, max %+.1f%%', $dev->min(), $dev->max()));
        }
        $this->info('File SQL: ' . $hasil['file']);

        return $hasil;
    }
}
