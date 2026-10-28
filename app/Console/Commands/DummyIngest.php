<?php

namespace App\Console\Commands;

/**
 * Generate + eksekusi sekaligus: dummy:generate, tampilkan ringkasan, konfirmasi, lalu dummy:execute.
 * File SQL tetap disimpan di database/dummy/output sebagai arsip / untuk rollback manual.
 * Contoh: php artisan dummy:ingest
 *         php artisan dummy:ingest --to=2026-11-05 --seed=123 --force
 */
class DummyIngest extends DummyGenerate
{
    protected $signature = 'dummy:ingest
                            {--from= : Tanggal mulai Y-m-d (default: tanggal penjualan terakhir + 1)}
                            {--to= : Tanggal akhir Y-m-d (default: hari ini, dibuat 1 hari penuh)}
                            {--seed= : Seed random agar hasil bisa diulang persis}
                            {--sampai-jam-sekarang : Transaksi hari ini hanya s/d jam sekarang}
                            {--quiet-days : Jangan tampilkan ringkasan per hari}
                            {--no-sp : Jangan jalankan SP setelah insert}
                            {--force : Langsung eksekusi tanpa konfirmasi}';

    protected $description = 'Generate file SQL data dummy lalu langsung eksekusi ke database (generate + execute)';

    public function handle()
    {
        $hasil = $this->generate();

        if (!$this->option('force') && !$this->confirm('Ringkasan di atas sudah sesuai? Lanjut eksekusi ke database?')) {
            $this->warn('Eksekusi dibatalkan. File SQL tetap tersimpan, bisa dijalankan nanti dengan dummy:execute.');
            return 1;
        }

        return $this->call('dummy:execute', [
            'file' => $hasil['file'],
            '--no-sp' => $this->option('no-sp'),
            '--force' => true,
        ]);
    }
}
