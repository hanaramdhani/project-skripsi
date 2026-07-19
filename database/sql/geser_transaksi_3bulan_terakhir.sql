-- =====================================================================
-- GESER SELURUH DATA TRANSAKSI KE 3 BULAN TERAKHIR
-- Engine: SQL Server (sqlsrv)
--
-- Tujuan:
--   Data transaksi saat ini berkisar 2024-01-01 s/d 2024-03-08. Script ini
--   menggeser SEMUA tanggal transaksi (beserta no_transaksi yang meng-embed
--   tanggal) sehingga transaksi TERAKHIR jatuh pada HARI INI. Dengan begitu
--   data akan tersebar pada kira-kira 3 bulan terakhir relatif hari eksekusi.
--
-- Format no_transaksi:
--   - t_penjualan / t_pembelian / t_pendapatan / t_biaya_operasional:
--        'HM' + yyMMdd + <seq 4 digit>   contoh: HM2403080043  (2024-03-08)
--   - t_modal:
--        'MOD' + yy + <seq 4 digit>      contoh: MOD240001      (tahun 2024)
--   - jurnal_umum.no_bukti ikut format di atas (HM.. / MOD..).
--
-- Pergeseran dihitung dalam HARI (bukan bulan) agar bersifat 1-1 (injective):
--   pemetaan bulanan bisa bentrok saat hari akhir bulan (mis. 31 Jan -> 30 Apr)
--   sehingga berisiko membuat no_transaksi kembar. Geser per-hari aman.
--
-- CATATAN PENTING:
--   * BACKUP dulu sebelum menjalankan.
--   * AMAN DIJALANKAN ULANG (mis. tiap bulan): @days dihitung dinamis dari
--     MAX(tanggal) penjualan -> hari ini, jadi tiap eksekusi menggeser ulang
--     seluruh data agar transaksi terakhir jatuh pada tanggal eksekusi.
--     - Day-shift bersifat 1-1, tidak akan membuat no_transaksi kembar.
--     - Dijalankan 2x di hari yang sama = no-op (@days = 0).
--   * Script dibungkus TRANSACTION. Periksa hasil query verifikasi di bagian
--     bawah, lalu HAPUS komentar pada COMMIT (atau ROLLBACK) sesuai keputusan.
--   * t_hutang_pajak & t_pembayaran_pajak SENGAJA tidak digeser (data pajak
--     turunan). Setiap kali menggeser data, sebaiknya reset & generate ulang
--     data pajak lewat blok OPSIONAL di paling bawah agar periode pajak sinkron.
-- =====================================================================

-- SET XACT_ABORT ON;
-- SET NOCOUNT ON;
-- BEGIN TRANSACTION;

-- ---------------------------------------------------------------------
-- 1. Hitung besar pergeseran (dalam HARI).
--    Acuan: transaksi penjualan terbaru dipetakan ke tanggal HARI INI.
-- ---------------------------------------------------------------------
DECLARE @days INT = DATEDIFF(DAY, (SELECT MAX(tanggal) FROM t_penjualan), CAST(GETDATE() AS DATE));
PRINT 'Pergeseran (hari): ' + CAST(@days AS varchar(10));

-- ---------------------------------------------------------------------
-- 2. TABEL PENJUALAN (header + detail)
-- ---------------------------------------------------------------------
-- 2a. Header: geser no_transaksi (re-encode yyMMdd) + semua kolom tanggal
UPDATE t_penjualan
SET
    no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi)),
    tanggal              = DATEADD(DAY, @days, tanggal),
    tanggal_jatuh_tempo  = DATEADD(DAY, @days, tanggal_jatuh_tempo),
    tanggal_setor        = DATEADD(DAY, @days, tanggal_setor),
    tanggal_server       = DATEADD(DAY, @days, tanggal_server)
WHERE no_transaksi LIKE 'HM%';

-- 2b. Detail: hanya no_transaksi (re-encode dari kode itu sendiri -> tetap sinkron dgn header)
UPDATE t_penjualan_detail
SET no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi))
WHERE no_transaksi LIKE 'HM%';

-- ---------------------------------------------------------------------
-- 3. TABEL PEMBELIAN (header + detail)
-- ---------------------------------------------------------------------
UPDATE t_pembelian
SET
    no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi)),
    tanggal              = DATEADD(DAY, @days, tanggal),
    tanggal_jatuh_tempo  = DATEADD(DAY, @days, tanggal_jatuh_tempo),
    tanggal_server       = DATEADD(DAY, @days, tanggal_server)
WHERE no_transaksi LIKE 'HM%';

UPDATE t_pembelian_detail
SET no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi))
WHERE no_transaksi LIKE 'HM%';

-- ---------------------------------------------------------------------
-- 4. TABEL PENDAPATAN
-- ---------------------------------------------------------------------
UPDATE t_pendapatan
SET
    no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi)),
    tanggal        = DATEADD(DAY, @days, tanggal),
    tanggal_server = DATEADD(DAY, @days, tanggal_server)
WHERE no_transaksi LIKE 'HM%';

-- ---------------------------------------------------------------------
-- 5. TABEL BIAYA OPERASIONAL
-- ---------------------------------------------------------------------
UPDATE t_biaya_operasional
SET
    no_transaksi = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_transaksi, 3, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 5, 2) AS INT),
              CAST(SUBSTRING(no_transaksi, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_transaksi, 9, LEN(no_transaksi)),
    tanggal        = DATEADD(DAY, @days, tanggal),
    tanggal_server = DATEADD(DAY, @days, tanggal_server)
WHERE no_transaksi LIKE 'HM%';

-- ---------------------------------------------------------------------
-- 6. TABEL MODAL  (format 'MOD' + yy + seq; hanya tahun yang di-embed)
-- ---------------------------------------------------------------------
UPDATE t_modal
SET
    no_transaksi = 'MOD'
        + FORMAT(DATEADD(DAY, @days, tanggal), 'yy')
        + SUBSTRING(no_transaksi, 6, LEN(no_transaksi)),
    tanggal = DATEADD(DAY, @days, tanggal)
WHERE no_transaksi LIKE 'MOD%';

-- ---------------------------------------------------------------------
-- 7. JURNAL UMUM (no_bukti mengacu ke no_transaksi; tgl_jurnal ikut geser)
--    Hanya baris HM../MOD.. yang digeser. Baris HPJ.. (pajak) dibiarkan.
-- ---------------------------------------------------------------------
-- 7a. no_bukti format HM (re-encode dari kode)
UPDATE jurnal_umum
SET no_bukti = 'HM'
        + FORMAT(DATEADD(DAY, @days, DATEFROMPARTS(
              2000 + CAST(SUBSTRING(no_bukti, 3, 2) AS INT),
              CAST(SUBSTRING(no_bukti, 5, 2) AS INT),
              CAST(SUBSTRING(no_bukti, 7, 2) AS INT))), 'yyMMdd')
        + SUBSTRING(no_bukti, 9, LEN(no_bukti))
WHERE no_bukti LIKE 'HM%';

-- 7b. no_bukti format MOD (tahun diambil dari tgl_jurnal yg belum digeser)
UPDATE jurnal_umum
SET no_bukti = 'MOD'
        + FORMAT(DATEADD(DAY, @days, tgl_jurnal), 'yy')
        + SUBSTRING(no_bukti, 6, LEN(no_bukti))
WHERE no_bukti LIKE 'MOD%';

-- 7c. tgl_jurnal untuk baris HM../MOD.. (HPJ.. dibiarkan mengikuti data pajak)
UPDATE jurnal_umum
SET tgl_jurnal = DATEADD(DAY, @days, tgl_jurnal)
WHERE no_bukti LIKE 'HM%' OR no_bukti LIKE 'MOD%';

-- =====================================================================
-- VERIFIKASI (periksa sebelum COMMIT)
-- =====================================================================
-- Rentang tanggal baru per tabel
SELECT 't_penjualan'  AS tabel, CONVERT(varchar(10), MIN(tanggal), 120) AS tgl_min, CONVERT(varchar(10), MAX(tanggal), 120) AS tgl_max FROM t_penjualan
UNION ALL SELECT 't_pembelian', CONVERT(varchar(10), MIN(tanggal),120), CONVERT(varchar(10), MAX(tanggal),120) FROM t_pembelian
UNION ALL SELECT 't_pendapatan', CONVERT(varchar(10), MIN(tanggal),120), CONVERT(varchar(10), MAX(tanggal),120) FROM t_pendapatan
UNION ALL SELECT 't_biaya_operasional', CONVERT(varchar(10), MIN(tanggal),120), CONVERT(varchar(10), MAX(tanggal),120) FROM t_biaya_operasional
UNION ALL SELECT 't_modal', CONVERT(varchar(10), MIN(tanggal),120), CONVERT(varchar(10), MAX(tanggal),120) FROM t_modal
UNION ALL SELECT 'jurnal_umum', CONVERT(varchar(10), MIN(tgl_jurnal),120), CONVERT(varchar(10), MAX(tgl_jurnal),120) FROM jurnal_umum;

-- Cek tidak ada no_transaksi kembar (harus 0 baris)
SELECT no_transaksi, COUNT(*) c FROM t_penjualan GROUP BY no_transaksi HAVING COUNT(*) > 1;
SELECT no_transaksi, COUNT(*) c FROM t_pembelian GROUP BY no_transaksi HAVING COUNT(*) > 1;

-- Cek detail tidak yatim (harus 0)
SELECT COUNT(*) AS penjualan_detail_yatim FROM t_penjualan_detail d
    WHERE NOT EXISTS (SELECT 1 FROM t_penjualan h WHERE h.no_transaksi = d.no_transaksi);
SELECT COUNT(*) AS pembelian_detail_yatim FROM t_pembelian_detail d
    WHERE NOT EXISTS (SELECT 1 FROM t_pembelian h WHERE h.no_transaksi = d.no_transaksi);

-- =====================================================================
-- Setelah verifikasi OK, HAPUS komentar salah satu baris berikut:
-- =====================================================================
-- COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;


-- =====================================================================
-- OPSIONAL: reset data pajak agar bisa di-generate ulang untuk data baru.
-- (Jalankan setelah COMMIT di atas, atau gabungkan ke dalam transaksi.)
-- Untuk auto-insert pembayarannya, jalankan: insert_pembayaran_pajak_auto.sql
-- =====================================================================
DELETE FROM T_PEMBAYARAN_PAJAK;
DELETE FROM T_HUTANG_PAJAK;
EXEC SP_GEnerateHutangPPhFinal;

INSERT INTO t_pembayaran_pajak (tanggal, masa_pajak, jenis_pajak, nominal, ntpn, periode, reff_no)
SELECT
    x.tanggal,
    FORMAT(x.tgl_pajak, 'yyyy-MM')                                 AS masa_pajak,
    x.jenis_pajak,
    x.nominal,
    'PPH' + FORMAT(x.tanggal, 'yyMMdd')
          + RIGHT('000' + CAST(
                ROW_NUMBER() OVER (PARTITION BY x.tanggal ORDER BY x.no_transaksi)
                AS varchar(3)), 3)                                  AS ntpn,
    DATEFROMPARTS(YEAR(x.tgl_pajak), MONTH(x.tgl_pajak), 1)        AS periode,
    x.no_transaksi                                                 AS reff_no
FROM (
    SELECT
        h.no_transaksi,
        h.tgl_pajak,
        h.jenis_pajak,
        h.nominal,
        -- tanggal 5 pada bulan tgl_pajak
        DATEFROMPARTS(YEAR(h.tgl_pajak), MONTH(h.tgl_pajak), 5) AS tanggal
    FROM t_hutang_pajak h
    WHERE NOT EXISTS (
        SELECT 1 FROM t_pembayaran_pajak p WHERE p.reff_no = h.no_transaksi
    )
    -- Aktifkan baris di bawah bila TIDAK ingin membuat pembayaran bertanggal masa depan:
    -- AND DATEFROMPARTS(YEAR(h.tgl_pajak), MONTH(h.tgl_pajak), 5) <= CAST(GETDATE() AS date)
) x;
-- lalu jalankan file: database/sql/insert_pembayaran_pajak_auto.sql
