/*
  Perbaikan HPP penjualan: HPP = qty (satuan terkecil) x HARGA BELI RATA-RATA per barang
  (total nilai beli / total qty beli), bukan harga_beli_terakhir.
  Akibatnya nilai persediaan = qty sisa x harga rata-rata: tidak pernah minus, stok 0 => nilai 0.
  Hanya #avg_beli (baru), #debit_hpp dan #kredit_persediaan_hpp yang berubah; bagian lain sama dengan SP asli.
  Jika barang belum pernah dibeli, dipakai harga_beli_terakhir pada baris penjualan.
*/
USE [skripsi_pos]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[sp_Generate_Jurnal_Umum]
    @TglAwal  DATE = NULL,
    @TglAkhir DATE = NULL
WITH RECOMPILE
AS
BEGIN
    SET NOCOUNT ON;

	IF @TglAwal IS NULL OR @TglAkhir IS NULL
	BEGIN
		SET @TglAwal = '2001-01-01'
		SET @TglAkhir = DATEADD(HOUR,8,GETUTCDATE())
	END

	SET @TglAkhir = DATEADD(DAY,1,@TglAkhir)

    DELETE FROM jurnal_umum
    WHERE tgl_jurnal BETWEEN @TglAwal AND @TglAkhir

    /* ======================
       PENJUALAN
       ====================== */

    -- Debit Kas
	DROP TABLE IF EXISTS #debit_kas
    SELECT
        p.tanggal,
        p.no_transaksi,
        '101' coa, -- Kas
        SUM(d.harga_jual*qty) debit,
        0 kredit,
        'Penjualan' sumber
	INTO #debit_kas
    FROM t_penjualan p WITH (NOLOCK)
    JOIN t_penjualan_detail d WITH (NOLOCK) ON p.no_transaksi = d.no_transaksi
	JOIN m_barang_satuan mbs WITH (NOLOCK) ON d.kd_barang=mbs.kd_barang AND d.kd_satuan=mbs.kd_satuan
    WHERE p.tanggal BETWEEN @TglAwal AND @TglAkhir
    GROUP BY p.tanggal, p.no_transaksi;


    -- Kredit Pendapatan
	DROP TABLE IF EXISTS #kredit_pendapatan
    SELECT
        p.tanggal,
        p.no_transaksi,
        '401' AS Coa, -- Pendapatan
        0 debit,
        SUM(d.harga_jual*qty) AS kredit,
        'Penjualan' AS sumber
	INTO #kredit_pendapatan
    FROM t_penjualan p  WITH (NOLOCK)
    JOIN t_penjualan_detail d  WITH (NOLOCK) ON p.no_transaksi = d.no_transaksi
	JOIN m_barang_satuan mbs WITH (NOLOCK) ON d.kd_barang=mbs.kd_barang AND d.kd_satuan=mbs.kd_satuan
    WHERE p.tanggal BETWEEN @TglAwal AND @TglAkhir
    GROUP BY p.tanggal, p.no_transaksi;


    /* ======================
       PEMBELIAN
       ====================== */

    -- Debit Persediaan
	DROP TABLE IF EXISTS #debit_persediaan
    SELECT
        b.tanggal,
        b.no_transaksi,
        '103' AS coa, -- Persediaan
        SUM(d.qty*jumlah * d.harga_beli/jumlah) AS debit,
        0 AS kredit,
        'Pembelian' AS sumber
	INTO #debit_persediaan
    FROM t_pembelian b WITH (NOLOCK)
    JOIN t_pembelian_detail d WITH (NOLOCK)  ON b.no_transaksi = d.no_transaksi
	INNER JOIN m_barang_satuan mbs WITH (NOLOCK)  ON d.kd_barang=mbs.kd_barang AND d.kd_satuan=mbs.kd_satuan
    WHERE b.tanggal BETWEEN @TglAwal AND @TglAkhir
    GROUP BY b.tanggal, b.no_transaksi;

    -- Kredit Kas
	DROP TABLE IF EXISTS #kredit_kas
    SELECT
        b.tanggal,
        b.no_transaksi,
        '101' AS coa, -- Kas
        0 AS debit,
        SUM(d.qty*jumlah * d.harga_beli/jumlah) kredit,
        'Pembelian' AS sumber
	INTO #kredit_kas
    FROM t_pembelian b WITH (NOLOCK)
    JOIN t_pembelian_detail d WITH (NOLOCK)  ON b.no_transaksi = d.no_transaksi
	INNER JOIN m_barang_satuan mbs WITH (NOLOCK)  ON d.kd_barang=mbs.kd_barang AND d.kd_satuan=mbs.kd_satuan
    WHERE b.tanggal BETWEEN @TglAwal AND @TglAkhir
    GROUP BY b.tanggal, b.no_transaksi;

    /* ======================
       BIAYA OPERASIONAL
       ====================== */

    -- Debit Beban
	DROP TABLE IF EXISTS #debit_beban
    SELECT
        tanggal,
        no_transaksi,
        '501' AS coa,
        nominal AS debit,
        0 AS kredit,
        'Biaya' AS sumber
	INTO #debit_beban
    FROM t_biaya_operasional WITH (NOLOCK)
    WHERE tanggal BETWEEN @TglAwal AND @TglAkhir

    -- Kredit Kas
	DROP TABLE IF EXISTS #kredit_kas_biaya
    SELECT
        tanggal,
        no_transaksi,
        '101' coa,
        0 debit,
        nominal AS kredit,
        'Biaya' AS sumber
	INTO #kredit_kas_biaya
    FROM t_biaya_operasional WITH (NOLOCK)
    WHERE tanggal BETWEEN @TglAwal AND @TglAkhir

    /* ======================
       PENDAPATAN LAIN
       ====================== */

    -- Debit Kas
	DROP TABLE IF EXISTS #debit_kas_pendapatan
    SELECT
        tanggal,
        no_transaksi,
        '101' AS coa,
        nominal AS debit,
        0 AS kredit,
        'Pendapatan Lain' AS sUMBER
	INTO #debit_kas_pendapatan
    FROM t_pendapatan WITH (NOLOCK)
    WHERE tanggal BETWEEN @TglAwal AND @TglAkhir

    -- Kredit Pendapatan Lain
	DROP TABLE IF EXISTS #kredit_p_lain
    SELECT
        tanggal,
        no_transaksi,
        '402' coa,
        0 debit,
        nominal kredit,
        'Pendapatan Lain' AS sumber
	INTO #kredit_p_lain
    FROM t_pendapatan WITH (NOLOCK)
    WHERE tanggal BETWEEN @TglAwal AND @TglAkhir

	-- harga beli rata-rata per barang (satuan terkecil), seluruh pembelian s/d batas tanggal
	DROP TABLE IF EXISTS #avg_beli

	SELECT
		d.kd_barang,
		SUM(d.qty * d.harga_beli) / NULLIF(SUM(d.qty * mbs.jumlah), 0) AS avg_beli
	INTO #avg_beli
	FROM t_pembelian b WITH (NOLOCK)
	JOIN t_pembelian_detail d WITH (NOLOCK) ON b.no_transaksi = d.no_transaksi
	JOIN m_barang_satuan mbs WITH (NOLOCK) ON d.kd_barang = mbs.kd_barang AND d.kd_satuan = mbs.kd_satuan
	WHERE b.tanggal < @TglAkhir
	GROUP BY d.kd_barang;

	-- hpp debit
	
	DROP TABLE IF EXISTS #debit_hpp

	SELECT
		p.tanggal,
		p.no_transaksi,
		'503' AS coa,
		SUM(
			d.qty * ISNULL(mbs.jumlah, 1) *
			ISNULL(av.avg_beli, ISNULL(d.harga_beli_terakhir,0))
		) AS debit,
		0 AS kredit,
		'HPP Penjualan' AS sumber
	INTO #debit_hpp
	FROM t_penjualan p WITH (NOLOCK) 
	JOIN t_penjualan_detail d
		ON p.no_transaksi = d.no_transaksi
	LEFT JOIN m_barang_satuan mbs WITH (NOLOCK)
		ON d.kd_barang = mbs.kd_barang AND d.kd_satuan = mbs.kd_satuan
	LEFT JOIN #avg_beli av
		ON av.kd_barang = d.kd_barang

	WHERE p.tanggal BETWEEN @TglAwal AND @TglAkhir

	GROUP BY
		p.tanggal,
		p.no_transaksi;

	-- #hpp_kredit
	DROP TABLE IF EXISTS #kredit_persediaan_hpp

	SELECT
		p.tanggal,
		p.no_transaksi,
		'103' AS coa,
		0 AS debit,
		SUM(
			d.qty * ISNULL(mbs.jumlah, 1) *
			ISNULL(av.avg_beli, ISNULL(d.harga_beli_terakhir,0))
		) AS kredit,
		'HPP Penjualan' AS sumber
	INTO #kredit_persediaan_hpp
	FROM t_penjualan p WITH (NOLOCK) 
	JOIN t_penjualan_detail d WITH (NOLOCK) 
		ON p.no_transaksi = d.no_transaksi
	LEFT JOIN m_barang_satuan mbs WITH (NOLOCK)
		ON d.kd_barang = mbs.kd_barang AND d.kd_satuan = mbs.kd_satuan
	LEFT JOIN #avg_beli av
		ON av.kd_barang = d.kd_barang

	WHERE p.tanggal BETWEEN @TglAwal AND @TglAkhir

	GROUP BY
		p.tanggal,
		p.no_transaksi;



	-- ###################################################### hutang pajak pph #############################################################
	DROP TABLE IF EXISTS #debit_beban_pph

	-- beban pph
	SELECT
		tgl_pajak,
		no_transaksi,
		'502' AS coa,
		nominal AS debit,
		0 AS kredit,
		'PPh Final' AS sumber
	INTO #debit_beban_pph
	FROM t_hutang_pajak WITH (NOLOCK)
	WHERE jenis_pajak = 'PPh Final UMKM 0.5%'
	  AND tgl_pajak BETWEEN @TglAwal AND @TglAkhir

	-- hutang
	DROP TABLE IF EXISTS #kredit_hutang_pph

	SELECT
		tgl_pajak,
		no_transaksi,
		'202' AS coa,
		0 AS debit,
		nominal AS kredit,
		'PPh Final' AS sumber
	INTO #kredit_hutang_pph
	FROM t_hutang_pajak WITH (NOLOCK)
	WHERE jenis_pajak = 'PPh Final UMKM 0.5%'
	  AND tgl_pajak BETWEEN @TglAwal AND @TglAkhir

	-- ###################################################### hutang pajak pph #############################################################

	--######## Pembayaran Pajak Final UMKM 0.5 ##########

	-- hutang berkurang -> debit

	DROP TABLE IF EXISTS #debit_hutang_pajak_bayar
	SELECT
		tanggal,
		CONCAT(reff_no,'-BYR') AS no_transaksi,
		'202' AS coa,
		nominal AS debit,
		0 AS kredit,
		'Pembayaran PPh Final' AS sumber
	INTO #debit_hutang_pajak_bayar
	FROM t_pembayaran_pajak WITH (NOLOCK)
	WHERE jenis_pajak = 'PPh Final UMKM 0.5%'
	  --AND dibayar > 0
	  --AND status_pajak = 1
	  AND tanggal BETWEEN @TglAwal AND @TglAkhir

	-- kas berkurang ->kredit
	DROP TABLE IF EXISTS #kredit_kas_pajak_bayar

	SELECT
		tanggal AS tanggal,
		CONCAT(reff_no,'-BYR') AS no_transaksi,
		'101' AS coa,
		0 AS debit,
		nominal AS kredit,
		'Pembayaran PPh Final' AS sumber
	INTO #kredit_kas_pajak_bayar
	FROM t_pembayaran_pajak WITH (NOLOCK)
	WHERE jenis_pajak = 'PPh Final UMKM 0.5%'
	  --AND dibayar > 0
	  --AND status_pajak = 1
	  AND tanggal BETWEEN @TglAwal AND @TglAkhir

	DROP TABLE IF EXISTS #debit_kas_modal

	SELECT
		tanggal,
		no_transaksi,
		'101' AS coa,
		nominal AS debit,
		0 AS kredit,
		'Modal' AS sumber
	INTO #debit_kas_modal
	FROM t_modal WITH (NOLOCK)
	WHERE tanggal BETWEEN @TglAwal AND @TglAkhir;

	DROP TABLE IF EXISTS #kredit_modal

	SELECT
		tanggal,
		no_transaksi,
		'301' AS coa,
		0 AS debit,
		nominal AS kredit,
		'Modal' AS sumber
	INTO #kredit_modal
	FROM t_modal WITH (NOLOCK)
	WHERE tanggal BETWEEN @TglAwal AND @TglAkhir;


	INSERT INTO jurnal_umum SELECT * FROM #debit_kas
	INSERT INTO jurnal_umum SELECT * FROM #kredit_pendapatan
	INSERT INTO jurnal_umum SELECT * FROM #debit_persediaan
	INSERT INTO jurnal_umum SELECT * FROM #kredit_kas
	INSERT INTO jurnal_umum SELECT * FROM #debit_beban
	INSERT INTO jurnal_umum SELECT * FROM #kredit_kas_biaya
	INSERT INTO jurnal_umum SELECT * FROM #debit_kas_pendapatan
	INSERT INTO jurnal_umum SELECT * FROM #kredit_p_lain
	INSERT INTO jurnal_umum SELECT * FROM #debit_hpp
	INSERT INTO jurnal_umum SELECT * FROM #kredit_persediaan_hpp
	INSERT INTO jurnal_umum SELECT * FROM #debit_beban_pph
	INSERT INTO jurnal_umum SELECT * FROM #kredit_hutang_pph
	INSERT INTO jurnal_umum SELECT * FROM #debit_hutang_pajak_bayar
	INSERT INTO jurnal_umum SELECT * FROM #kredit_kas_pajak_bayar
	INSERT INTO jurnal_umum
	SELECT * FROM #debit_kas_modal;

	INSERT INTO jurnal_umum
	SELECT * FROM #kredit_modal;

END;
GO
