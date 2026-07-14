/* ============================================================================
   MIGRASI SKEMA — penyesuaian tabel master untuk DataTables server-side + CRUD
   Database : skripsi_pos  (Microsoft SQL Server)
   Sifat    : IDEMPOTEN — aman dijalankan berulang & di lingkungan lain (SSMS/sqlcmd).
   Tujuan   : menyamakan skema DB dengan yang diharapkan form/CRUD aplikasi.
   ----------------------------------------------------------------------------
   Ringkasan perubahan:
     A. Tambah kolom date_add (audit) di 6 tabel.
     B. Longgarkan kolom NOT NULL yang tidak diisi form menjadi NULL.
     C. Rename kolom: m_kas.no_rekening->nama, m_biaya/m_pendapatan.kd_index->kd_akun.
     D. Tambah kolom m_barang_satuan.keterangan.
     E. Prasyarat tabel: m_jabatan (sudah dibuat manual) & m_akun (diperlukan
        oleh JOIN pada halaman Biaya/Pendapatan/Pegawai).
   ============================================================================ */
GO

/* ---------------------------------------------------------------------------
   A. Kolom date_add (datetime, nullable)
   --------------------------------------------------------------------------- */
IF COL_LENGTH('m_barang',   'date_add') IS NULL ALTER TABLE m_barang   ADD date_add datetime NULL;
IF COL_LENGTH('m_kas',      'date_add') IS NULL ALTER TABLE m_kas      ADD date_add datetime NULL;
IF COL_LENGTH('m_divisi',   'date_add') IS NULL ALTER TABLE m_divisi   ADD date_add datetime NULL;
IF COL_LENGTH('m_customer', 'date_add') IS NULL ALTER TABLE m_customer ADD date_add datetime NULL;
IF COL_LENGTH('m_satuan',   'date_add') IS NULL ALTER TABLE m_satuan   ADD date_add datetime NULL;
IF COL_LENGTH('m_supplier', 'date_add') IS NULL ALTER TABLE m_supplier ADD date_add datetime NULL;
GO

/* ---------------------------------------------------------------------------
   C. Rename kolom  (dijalankan sebelum B agar nama kolom sudah final)
   --------------------------------------------------------------------------- */
-- m_kas.no_rekening -> nama
IF COL_LENGTH('m_kas','no_rekening') IS NOT NULL AND COL_LENGTH('m_kas','nama') IS NULL
    EXEC sp_rename 'm_kas.no_rekening', 'nama', 'COLUMN';
-- m_biaya.kd_index -> kd_akun
IF COL_LENGTH('m_biaya','kd_index') IS NOT NULL AND COL_LENGTH('m_biaya','kd_akun') IS NULL
    EXEC sp_rename 'm_biaya.kd_index', 'kd_akun', 'COLUMN';
-- m_pendapatan.kd_index -> kd_akun
IF COL_LENGTH('m_pendapatan','kd_index') IS NOT NULL AND COL_LENGTH('m_pendapatan','kd_akun') IS NULL
    EXEC sp_rename 'm_pendapatan.kd_index', 'kd_akun', 'COLUMN';
GO

/* ---------------------------------------------------------------------------
   D. Tambah kolom m_barang_satuan.keterangan
   --------------------------------------------------------------------------- */
IF COL_LENGTH('m_barang_satuan','keterangan') IS NULL
    ALTER TABLE m_barang_satuan ADD keterangan varchar(200) NULL;
GO

/* ---------------------------------------------------------------------------
   B. Longgarkan kolom NOT NULL -> NULL (tipe dipertahankan)
   --------------------------------------------------------------------------- */
-- m_barang
IF COL_LENGTH('m_barang','kd_kategori')    IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN kd_kategori char(6) NULL');
IF COL_LENGTH('m_barang','kd_jenis_bahan') IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN kd_jenis_bahan char(6) NULL');
IF COL_LENGTH('m_barang','kd_model')       IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN kd_model char(6) NULL');
IF COL_LENGTH('m_barang','kd_merk')        IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN kd_merk char(6) NULL');
IF COL_LENGTH('m_barang','kd_warna')       IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN kd_warna char(6) NULL');
IF COL_LENGTH('m_barang','ukuran')         IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN ukuran float NULL');
IF COL_LENGTH('m_barang','status_pinjam')  IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN status_pinjam tinyint NULL');
IF COL_LENGTH('m_barang','pabrik')         IS NOT NULL EXEC('ALTER TABLE m_barang ALTER COLUMN pabrik tinyint NULL');

-- m_divisi
IF COL_LENGTH('m_divisi','kepala_nota')    IS NOT NULL EXEC('ALTER TABLE m_divisi ALTER COLUMN kepala_nota varchar(5) NULL');

-- m_customer
IF COL_LENGTH('m_customer','kd_kota')      IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN kd_kota char(6) NULL');
IF COL_LENGTH('m_customer','telepon')      IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN telepon varchar(10) NULL');
IF COL_LENGTH('m_customer','fax')          IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN fax varchar(10) NULL');
IF COL_LENGTH('m_customer','kontak')       IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN kontak varchar(35) NULL');
IF COL_LENGTH('m_customer','point')        IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN point money NULL');
IF COL_LENGTH('m_customer','limit_kredit') IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN limit_kredit money NULL');
IF COL_LENGTH('m_customer','disc')         IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN disc float NULL');
IF COL_LENGTH('m_customer','status')       IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN status tinyint NULL');
IF COL_LENGTH('m_customer','parent')       IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN parent varchar(50) NULL');
IF COL_LENGTH('m_customer','keterangan')   IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN keterangan varchar(200) NULL');
IF COL_LENGTH('m_customer','npwp_no')      IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN npwp_no varchar(50) NULL');
IF COL_LENGTH('m_customer','nppkp_no')     IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN nppkp_no varchar(50) NULL');
IF COL_LENGTH('m_customer','npwp_nama')    IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN npwp_nama varchar(50) NULL');
IF COL_LENGTH('m_customer','npwp_alamat')  IS NOT NULL EXEC('ALTER TABLE m_customer ALTER COLUMN npwp_alamat varchar(500) NULL');

-- m_supplier
IF COL_LENGTH('m_supplier','kd_kota')      IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN kd_kota char(6) NULL');
IF COL_LENGTH('m_supplier','telepon')      IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN telepon varchar(10) NULL');
IF COL_LENGTH('m_supplier','fax')          IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN fax varchar(10) NULL');
IF COL_LENGTH('m_supplier','kontak')       IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN kontak varchar(35) NULL');
IF COL_LENGTH('m_supplier','kd_bank')      IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN kd_bank char(6) NULL');
IF COL_LENGTH('m_supplier','rekening')     IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN rekening varchar(25) NULL');
IF COL_LENGTH('m_supplier','jenis')        IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN jenis tinyint NULL');
IF COL_LENGTH('m_supplier','keterangan')   IS NOT NULL EXEC('ALTER TABLE m_supplier ALTER COLUMN keterangan varchar(50) NULL');

-- m_kas  (kolom di luar yang diisi form)
IF COL_LENGTH('m_kas','kd_index')          IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN kd_index varchar(10) NULL');
IF COL_LENGTH('m_kas','kd_bank')           IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN kd_bank char(6) NULL');
IF COL_LENGTH('m_kas','kd_kota')           IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN kd_kota char(6) NULL');
IF COL_LENGTH('m_kas','telepon')           IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN telepon varchar(10) NULL');
IF COL_LENGTH('m_kas','kontak')            IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN kontak varchar(35) NULL');
IF COL_LENGTH('m_kas','cabang')            IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN cabang varchar(50) NULL');
IF COL_LENGTH('m_kas','saldo_awal')        IS NOT NULL EXEC('ALTER TABLE m_kas ALTER COLUMN saldo_awal money NULL');

-- m_barang_satuan
IF COL_LENGTH('m_barang_satuan','jumlah')  IS NOT NULL EXEC('ALTER TABLE m_barang_satuan ALTER COLUMN jumlah float NULL');

-- m_pegawai  (kolom di luar yang diisi form)
IF COL_LENGTH('m_pegawai','kd_jenis')      IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kd_jenis char(6) NULL');
IF COL_LENGTH('m_pegawai','kd_kota')       IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kd_kota char(6) NULL');
IF COL_LENGTH('m_pegawai','kd_agama')      IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kd_agama char(6) NULL');
IF COL_LENGTH('m_pegawai','kd_shift')      IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kd_shift char(6) NULL');
IF COL_LENGTH('m_pegawai','kd_divisi')     IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kd_divisi char(6) NULL');
IF COL_LENGTH('m_pegawai','tempat_lahir')  IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN tempat_lahir varchar(35) NULL');
IF COL_LENGTH('m_pegawai','tanggal_lahir') IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN tanggal_lahir datetime NULL');
IF COL_LENGTH('m_pegawai','alamat')        IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN alamat varchar(50) NULL');
IF COL_LENGTH('m_pegawai','telepon')       IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN telepon varchar(10) NULL');
IF COL_LENGTH('m_pegawai','hp')            IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN hp varchar(15) NULL');
IF COL_LENGTH('m_pegawai','ktp')           IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN ktp varchar(20) NULL');
IF COL_LENGTH('m_pegawai','tgl_masuk')     IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN tgl_masuk datetime NULL');
IF COL_LENGTH('m_pegawai','kelamin')       IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kelamin tinyint NULL');
IF COL_LENGTH('m_pegawai','kelompok')      IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN kelompok int NULL');
IF COL_LENGTH('m_pegawai','point')         IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN point int NULL');
IF COL_LENGTH('m_pegawai','status_kawin')  IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN status_kawin tinyint NULL');
IF COL_LENGTH('m_pegawai','status_lembur') IS NOT NULL EXEC('ALTER TABLE m_pegawai ALTER COLUMN status_lembur tinyint NULL');
GO

/* ---------------------------------------------------------------------------
   E. Prasyarat tabel master
   --------------------------------------------------------------------------- */
-- m_jabatan : sudah dibuat manual pada DB ini. Skrip di bawah dibuat idempoten
--             agar lingkungan lain yang belum punya tabelnya ikut terpasang.
IF OBJECT_ID('dbo.m_jabatan','U') IS NULL
BEGIN
    CREATE TABLE dbo.m_jabatan (
        kd_jabatan char(6)      NOT NULL PRIMARY KEY,
        nama       varchar(35)  NOT NULL,
        keterangan varchar(50)  NOT NULL,
        status     tinyint      NOT NULL,
        uang_makan money        NOT NULL
    );
END
GO

-- m_akun : DIPERLUKAN oleh halaman Biaya & Pendapatan (JOIN untuk kolom AKUN
--          dan dropdown pilih akun). Belum ada di DB ini. Struktur minimal:
IF OBJECT_ID('dbo.m_akun','U') IS NULL
BEGIN
    CREATE TABLE dbo.m_akun (
        kd_akun    varchar(10)  NOT NULL PRIMARY KEY,
        nama       varchar(50)  NOT NULL,
        keterangan varchar(100) NULL,
        status     tinyint      NULL
    );
END
GO

/* ============================================================================
   SELESAI
   Catatan: setelah migrasi, tabel yang belum berisi data referensi
   (m_akun, m_jabatan) perlu diisi datanya agar dropdown & JOIN menampilkan hasil.
   ============================================================================ */
