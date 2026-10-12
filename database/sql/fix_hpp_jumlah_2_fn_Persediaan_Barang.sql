/*
  Perbaikan HPP penjualan: nilai_hpp = qty jual (satuan terkecil) x harga beli rata-rata
  (nilai_beli / qty_beli, s/d @TglAkhir), sama dengan sp_Generate_Jurnal_Umum.
  Hanya CTE "jual" yang berubah; kolom keluaran tidak berubah.
*/
USE [skripsi_pos]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER FUNCTION dbo.fn_Persediaan_Barang
(
    @TglAkhir DATE
)
RETURNS TABLE
AS
RETURN
(
    WITH beli AS (   -- sama dengan #debit_persediaan (sumber 'Pembelian')
        SELECT d.kd_barang,
               SUM(d.qty * s.jumlah)     AS qty_beli,
               SUM(d.qty * d.harga_beli) AS nilai_beli
        FROM t_pembelian b
        JOIN t_pembelian_detail d ON d.no_transaksi = b.no_transaksi
        JOIN m_barang_satuan s    ON s.kd_barang = d.kd_barang AND s.kd_satuan = d.kd_satuan
        WHERE b.tanggal < DATEADD(DAY, 1, @TglAkhir)
        GROUP BY d.kd_barang
    ),
    jual AS (        -- sama dengan #kredit_persediaan_hpp (sumber 'HPP Penjualan')
        SELECT d.kd_barang,
               SUM(d.qty * ISNULL(s.jumlah, 1)) AS qty_jual,
               SUM(d.qty * ISNULL(s.jumlah, 1)
                   * ISNULL(b.nilai_beli / NULLIF(b.qty_beli, 0), ISNULL(d.harga_beli_terakhir, 0))) AS nilai_hpp
        FROM t_penjualan p
        JOIN t_penjualan_detail d ON d.no_transaksi = p.no_transaksi
        LEFT JOIN m_barang_satuan s ON s.kd_barang = d.kd_barang AND s.kd_satuan = d.kd_satuan
        LEFT JOIN beli b ON b.kd_barang = d.kd_barang
        WHERE p.tanggal < DATEADD(DAY, 1, @TglAkhir)
        GROUP BY d.kd_barang
    )
    SELECT k.kd_barang,
           m.nama,
           sk.nama                 AS satuan_terkecil,
           ISNULL(b.qty_beli, 0)   AS qty_beli,
           ISNULL(b.nilai_beli, 0) AS nilai_beli,
           ISNULL(j.qty_jual, 0)   AS qty_jual,
           ISNULL(j.nilai_hpp, 0)  AS nilai_hpp,
           ISNULL(b.qty_beli, 0)   - ISNULL(j.qty_jual, 0)  AS qty_sisa,
           ISNULL(b.nilai_beli, 0) - ISNULL(j.nilai_hpp, 0) AS nilai_persediaan
    FROM (SELECT kd_barang FROM beli UNION SELECT kd_barang FROM jual) k
    LEFT JOIN beli b ON b.kd_barang = k.kd_barang
    LEFT JOIN jual j ON j.kd_barang = k.kd_barang
    LEFT JOIN m_barang m ON m.kd_barang = k.kd_barang
    LEFT JOIN m_barang_satuan bs ON bs.kd_barang = k.kd_barang AND bs.jumlah = 1
    LEFT JOIN m_satuan sk ON sk.kd_satuan = bs.kd_satuan
);
GO
