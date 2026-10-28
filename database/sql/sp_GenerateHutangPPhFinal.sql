USE [skripsi_pos]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =====================================================================================
-- Generate hutang PPh Final untuk SEMUA bulan sebelum bulan @Tanggal.
--   EXEC sp_GenerateHutangPPhFinal '2026-10-28'  -> semua periode s/d September 2026
--
--   * Penjualan di bulan yang sama dengan @Tanggal tidak ikut dihitung.
--   * Per bulan periode:
--       tgl_pajak    = tanggal 1 bulan berikutnya
--       no_transaksi = HPJ + yyyyMM bulan berikutnya + 001
--       jatuh_tempo  = akhir bulan periode + 20 hari
--   * Bulan yang hutangnya sudah ada tidak dibuat lagi.
--   * Bulan tanpa penjualan -> tidak ada record.
--   * @Tanggal NULL -> pakai GETDATE().
-- =====================================================================================
CREATE OR ALTER PROCEDURE [dbo].[sp_GenerateHutangPPhFinal](@Tanggal DATE = NULL)
AS
BEGIN

    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    IF @Tanggal IS NULL
        SET @Tanggal = GETDATE();

    DECLARE @BatasAkhir DATE        = DATEFROMPARTS(YEAR(@Tanggal), MONTH(@Tanggal), 1); -- tgl 1 bulan parameter (exclusive)
    DECLARE @JenisPajak VARCHAR(50) = 'PPh Final UMKM 0.5%';

    BEGIN TRAN;

    WITH pph AS (
        SELECT
            CONCAT(
                'PPh Final periode ',
                DATENAME(MONTH, MAX(p.tanggal)),
                ' ',
                YEAR(MAX(p.tanggal))
            ) AS Periode,
            SUM(d.harga_jual * d.qty) AS Omzet,
            SUM(d.harga_jual * d.qty) * 0.0005 AS PPHFinal,
            DATEADD(DAY, 1, EOMONTH(MAX(p.tanggal))) AS TglPajak,
            DATEADD(DAY, 20, EOMONTH(MAX(p.tanggal))) AS JatuhTempo
        FROM t_penjualan p
        JOIN t_penjualan_detail d
        ON p.no_transaksi = d.no_transaksi
        JOIN m_barang_satuan mbs
        ON d.kd_barang = mbs.kd_barang
        AND d.kd_satuan = mbs.kd_satuan
        WHERE p.tanggal < @BatasAkhir
        GROUP BY YEAR(p.tanggal), MONTH(p.tanggal)
    )
    INSERT INTO t_hutang_pajak
    (
        no_transaksi,tgl_pajak,jatuh_tempo,jenis_pajak,
        coa_kode,omzet_last_month,nominal,
        status_pajak,keterangan
    )
    SELECT
        CONCAT('HPJ', FORMAT(t.TglPajak, 'yyyyMM'), '001'),
        t.TglPajak,
        t.JatuhTempo,
        @JenisPajak,
        '202',
        t.Omzet,
        t.PPHFinal,
        1,
        t.Periode
    FROM pph t
    WHERE NOT EXISTS (
        SELECT 1
        FROM t_hutang_pajak h WITH (UPDLOCK, HOLDLOCK)
        WHERE h.no_transaksi = CONCAT('HPJ', FORMAT(t.TglPajak, 'yyyyMM'), '001')
           OR (h.jenis_pajak = @JenisPajak
               AND h.tgl_pajak >= t.TglPajak
               AND h.tgl_pajak <  DATEADD(MONTH, 1, t.TglPajak))
    );

    COMMIT;

END
GO
