-- =====================================================================
-- Tabel transaksi Pembayaran Pajak
-- Engine target: SQL Server (sqlsrv)
--
-- Kolom ntpn diisi otomatis oleh aplikasi dengan format:
--   PPH + YYMMDD + 3 digit increment   contoh: PPH260519001
--   3 digit terakhir increment sesuai tanggal berjalan (per hari).
--
-- Catatan: jalankan file ini SEKALI saja.
-- =====================================================================

CREATE TABLE t_pembayaran_pajak
(
    id INT IDENTITY(1,1) PRIMARY KEY,
    tanggal DATETIME,
    masa_pajak VARCHAR(20),
    jenis_pajak VARCHAR(50),
    nominal DECIMAL(18,2),
    ntpn VARCHAR(100)
);
