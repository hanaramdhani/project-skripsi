/*
  Samakan rincian HPP per barang (modal di Laporan Laba Rugi) dengan sp_Generate_Jurnal_Umum:
  nilai_hpp = qty x ISNULL(jumlah,1) x harga beli rata-rata per barang
  (total nilai beli / total qty beli, seluruh pembelian); jika barang belum pernah dibeli
  dipakai harga_beli_terakhir pada baris penjualan.
  qty_jual dan nilai_penjualan tidak berubah; kolom keluaran sama.
*/
USE [skripsi_pos]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER FUNCTION dbo.fn_LabaRugi_PerBarang
(
    @TglAwal  DATE,
    @TglAkhir DATE
)
RETURNS TABLE
AS
RETURN
(
    WITH avg_beli AS (   -- sama dengan #avg_beli di sp_Generate_Jurnal_Umum
        SELECT d.kd_barang,
               SUM(d.qty * d.harga_beli) / NULLIF(SUM(d.qty * s.jumlah), 0) AS avg_beli
        FROM t_pembelian b
        JOIN t_pembelian_detail d ON d.no_transaksi = b.no_transaksi
        JOIN m_barang_satuan s    ON s.kd_barang = d.kd_barang AND s.kd_satuan = d.kd_satuan
        GROUP BY d.kd_barang
    ),
    jual AS (   -- sama dengan #kredit_pendapatan (401) dan #debit_hpp (503) di sp_Generate_Jurnal_Umum
        SELECT d.kd_barang,
               SUM(d.qty * ISNULL(s.jumlah, 1)) AS qty_jual,     -- dalam satuan terkecil
               SUM(CASE WHEN s.kd_barang IS NOT NULL THEN d.qty * d.harga_jual ELSE 0 END) AS nilai_penjualan,
               SUM(d.qty * ISNULL(s.jumlah, 1)
                   * ISNULL(a.avg_beli, ISNULL(d.harga_beli_terakhir, 0))) AS nilai_hpp
        FROM t_penjualan p
        JOIN t_penjualan_detail d   ON d.no_transaksi = p.no_transaksi
        LEFT JOIN m_barang_satuan s ON s.kd_barang = d.kd_barang AND s.kd_satuan = d.kd_satuan
        LEFT JOIN avg_beli a        ON a.kd_barang = d.kd_barang
        WHERE p.tanggal >= @TglAwal
          AND p.tanggal <  DATEADD(DAY, 1, @TglAkhir)
        GROUP BY d.kd_barang
    )
    SELECT j.kd_barang,
           m.nama,
           sk.nama                             AS satuan_terkecil,
           j.qty_jual,
           j.nilai_penjualan,
           j.nilai_hpp,
           j.nilai_penjualan - j.nilai_hpp     AS laba_kotor
    FROM jual j
    LEFT JOIN m_barang m         ON m.kd_barang = j.kd_barang
    LEFT JOIN m_barang_satuan bs ON bs.kd_barang = j.kd_barang AND bs.jumlah = 1
    LEFT JOIN m_satuan sk        ON sk.kd_satuan = bs.kd_satuan
);
GO
