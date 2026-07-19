-- =====================================================================
-- AUTO-INSERT PEMBAYARAN PAJAK dari t_hutang_pajak
-- Engine: SQL Server (sqlsrv)
--
-- Membuat 1 baris pembayaran (t_pembayaran_pajak) untuk setiap hutang pajak
-- yang BELUM dibayar, dengan asumsi:
--   * tanggal bayar  = tanggal 5 pada bulan tgl_pajak hutang
--     (tgl_pajak = tanggal 1 bulan berjalan, jadi dibayar 5 hari kemudian).
--   * nominal        = nominal hutang (t_hutang_pajak.nominal).
--   * jenis_pajak    = jenis_pajak hutang.
--   * periode/masa   = bulan tgl_pajak (mengikuti alur tombol Bayar di aplikasi).
--   * reff_no        = no_transaksi hutang.
--   * ntpn           = 'PPH' + yyMMdd(tanggal) + urutan 3 digit per hari.
--
-- Sifat:
--   * Idempotent: hanya insert untuk hutang yang belum punya pembayaran
--     (NOT EXISTS berdasar reff_no), jadi aman dijalankan berulang.
--   * Asumsi t_pembayaran_pajak tidak punya baris lain di hari yang sama
--     dengan tanggal-5 (agar NTPN tidak bentrok). Cocok dijalankan setelah
--     reset + EXEC sp_GenerateHutangPPhFinal.
--
-- CATATAN: BACKUP dulu. Periksa verifikasi lalu COMMIT / ROLLBACK.
-- =====================================================================

SET XACT_ABORT ON;
SET NOCOUNT ON;
BEGIN TRANSACTION;

-- ---------------------------------------------------------------------
-- 1. Insert pembayaran untuk tiap hutang yang belum dibayar
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- 2. Tandai hutang yang sudah ada pembayarannya sebagai lunas (status_pajak = 2)
-- ---------------------------------------------------------------------
UPDATE h
SET status_pajak = 2
FROM t_hutang_pajak h
WHERE EXISTS (SELECT 1 FROM t_pembayaran_pajak p WHERE p.reff_no = h.no_transaksi);

-- =====================================================================
-- VERIFIKASI (periksa sebelum COMMIT)
-- =====================================================================
SELECT
    p.ntpn,
    CONVERT(varchar(10), p.tanggal, 120) AS tgl_bayar,
    p.masa_pajak,
    CONVERT(varchar(10), p.periode, 120) AS periode,
    p.jenis_pajak,
    p.nominal,
    p.reff_no
FROM t_pembayaran_pajak p
ORDER BY p.tanggal;

-- Pastikan tidak ada NTPN kembar (harus 0 baris)
SELECT ntpn, COUNT(*) c FROM t_pembayaran_pajak GROUP BY ntpn HAVING COUNT(*) > 1;

-- =====================================================================
-- Setelah verifikasi OK, HAPUS komentar salah satu baris berikut:
-- =====================================================================
-- COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;
