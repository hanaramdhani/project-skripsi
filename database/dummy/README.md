# Ingest Data Dummy Transaksi

Generator data dummy untuk **penjualan, pembelian, dan biaya operasional**. Datanya mengikuti pola transaksi historis, dan **hanya memakai data master yang sudah ada**.

- Kode: [app/Support/DummyTransaksiGenerator.php](../../app/Support/DummyTransaksiGenerator.php)
- Command: `dummy:generate` dan `dummy:execute` (di [app/Console/Commands/](../../app/Console/Commands/))
- Output: `database/dummy/output/dummy_transaksi_<dari>_<sampai>_<waktu-generate>.sql`. Setiap kali generate, file barunya terpisah.

## Cara pakai (ingest ulang)

**Paling cepat: satu perintah.** Perintah ini generate, menampilkan ringkasan, minta konfirmasi, lalu eksekusi dan menjalankan SP:
```bash
php artisan dummy:ingest
#   opsinya sama dengan dummy:generate, ditambah:
#   --no-sp   tanpa menjalankan SP turunan
#   --force   langsung eksekusi tanpa konfirmasi
```
File SQL tetap tersimpan di `database/dummy/output/` sebagai arsip dan untuk rollback manual.

**Atau dua langkah terpisah**, kalau mau review file atau menjalankannya manual di SSMS:
```bash
# 1. Generate file SQL (belum menulis ke DB). Periode default:
#    tanggal penjualan terakhir + 1 hari  s/d  hari ini (1 hari penuh)
php artisan dummy:generate

#    opsi:
#    --from=2026-10-29 --to=2026-11-05   periode manual
#    --seed=123                          hasil bisa diulang persis
#    --sampai-jam-sekarang               transaksi hari ini hanya s/d jam sekarang
#    --quiet-days                        tanpa ringkasan per hari

# 2. Review ringkasan per hari di header file SQL, lalu eksekusi:
php artisan dummy:execute "database/dummy/output/dummy_transaksi_....sql"
#    --no-sp   tanpa menjalankan SP turunan
#    --force   tanpa konfirmasi
```

Setelah insert, SP turunan berikut dijalankan berurutan. Perintahnya, lengkap dengan tanggal, sudah tertulis di file SQL setelah `COMMIT`, di antara penanda `-- @sp-mulai` dan `-- @sp-selesai`:
1. `EXEC sp_harga_beli_terakhir`
2. `EXEC sp_Generate_Jurnal_Umum '<tanggal transaksi pertama>', '<sampai>'`. Jurnal dibuat ulang **dari awal**, bukan hanya periode ingest. Alasannya, HPP memakai harga beli rata-rata sampai tanggal akhir, jadi jurnal lama harus ikut dihitung ulang supaya persediaan di neraca (akun 103) sama dengan total rincian barang.
3. `EXEC sp_GenerateHutangPPhFinal '<tgl 1 bulan>'`, satu baris untuk setiap bulan dari bulan `<dari>` sampai bulan dari `<sampai>` + 1 hari.
   - SP ini membuat PPh untuk semua bulan **sebelum** bulan parameter. Bulan yang sudah punya baris PPh dilewati, jadi aman dijalankan berulang.
   - Contoh periode 15 Jan – 10 Jun: `'2026-01-01'` s/d `'2026-06-01'`. PPh terbentuk sampai Mei, karena Juni belum penuh.
   - Contoh periode 1 Jan – 30 Jun: `'2026-01-01'` s/d `'2026-07-01'`. Juni sudah penuh, jadi PPh Juni ikut dibuat.

Setelah itu `dummy:execute` menampilkan hasil validasi: jumlah barang dengan stok minus (harus 0), serta omzet dan nota per hari.

**Eksekusi manual di SSMS / sqlcmd:** buka file `.sql`, copy **seluruh isinya**, lalu jalankan sebagai **satu batch**. Insert dan SP ikut jalan semua. Kalau insert gagal, batch berhenti dan SP tidak ikut jalan.

> Jalankan file SQL segera setelah generate. Kalau di antara generate dan eksekusi ada transaksi baru yang mengubah stok, file akan membatalkan dirinya sendiri (rollback) bila hasilnya membuat stok minus. Generate ulang saja.

## Pengaman di file SQL

- Seluruh insert ada di dalam satu `BEGIN TRANSACTION`. Kalau satu gagal, semuanya di-rollback (`XACT_ABORT ON` + `TRY/CATCH`).
- Eksekusi dibatalkan kalau:
  - sudah ada penjualan di periode file tersebut, atau
  - nomor transaksinya sudah terpakai.
- Setelah insert dilakukan pengecekan `v_stok_barang`. Kalau ada stok < 0, semuanya di-rollback.
- Di bagian bawah file ada **query rollback manual** (dalam komentar). Query ini menghapus data file tersebut berdasarkan rentang nomor transaksi, menghapus baris PPh bulan-bulan yang terkena, lalu menjalankan ulang SP harga beli dan jurnal. SP PPh sengaja tidak dijalankan ulang, karena bulan yang datanya tinggal sebagian akan terhitung parsial.

## Aturan data

### Umum
| Hal | Aturan |
|---|---|
| Tabel yang diinsert | `t_penjualan`, `t_penjualan_detail`, `t_pembelian`, `t_pembelian_detail`, `t_biaya_operasional` |
| Tidak disentuh | `t_pendapatan` (dilewati), semua tabel master, transaksi yang sudah ada |
| Periode | tanggal penjualan terakhir + 1 hari s/d hari ini |
| No. transaksi | `HM` + `yymmdd` + nomor urut 4 digit per tabel per hari (pola data lama). App memakai prefix `PJ`/`BB`/`BO`, jadi tidak bentrok |
| kd_divisi / kd_jenis / kd_kas / kd_voucher | `DAA000` / `JAA000` / `KAA000` / `VAA000` |
| kd_pegawai (detail jual) | `PAA000` (m_pegawai kosong, kolom wajib diisi) |
| kd_user | penjualan: `UAA003` (kasir1, sebelum jam 15:00) dan `UAA004` (kasir2, sesudahnya). Pembelian & biaya: `UAA001`/`UAA002` (admin), acak |
| Barang | `m_barang.status` 1 dan 2 (status 2 tampil "Tidak Aktif" di UI tapi masih laku, misalnya gas 3kg dan rokok, sekitar 1/3 omzet). Satuan harus aktif di `m_barang_satuan` |
| Acuan pola | omzet & jumlah nota: 30 hari terakhir yang penuh (hari dengan nota < 50% median diabaikan). Bobot barang, qty, jam, item per nota, dan customer: 30 hari terakhir |

### Penjualan (wajib setiap hari)
- **Batas utama:** **rata-rata omzet per bulan** harus dalam **±10%** dari acuan. Omzet harian boleh naik-turun bebas, sekitar −55% sampai +55%, supaya grafik tidak terlihat direkayasa.
- **Target omzet harian** = acuan × faktor hari × faktor gajian × tren mingguan × acak harian × hari spesial:

  | Komponen | Aturan |
  |---|---|
  | Faktor hari | Sen ×1,05, Sel ×1,00, Rab ×0,88, Kam ×0,90, Jum ×1,00, **Sab ×1,10, Min ×1,12** (weekend paling ramai, Rabu dan Kamis sepi) |
  | Faktor gajian | tgl 1–3 ×1,15, tgl 4–7 ×1,06, tgl 8–14 ×1,00, tgl 15–24 ×0,93 (tanggal tua), tgl 25–27 ×1,00, tgl 28–akhir bulan ×1,08 (menjelang gajian) |
  | Tren mingguan | bergeser acak ±3% per minggu dan cenderung kembali ke acuan, maksimal ±6% |
  | Acak harian | distribusi normal dengan simpangan 6,5%, dipotong ±20% |
  | Hari sepi | peluang sekitar 2x per bulan, ×0,55–0,75 |
  | Hari ramai | peluang sekitar 2x per bulan, ×1,20–1,35 |
  | 17 Agustus | selalu ramai, ×1,25–1,35 |

- **Batas rencana:** rencana semua hari dibuat di awal. Kalau rata-rata rencana sebulan keluar dari ±6%, semua hari di bulan itu diskalakan. Polanya tetap, dan hasil aktualnya aman di dalam ±10%.
- **Pembentukan nota:** nota terus dibuat sampai target omzet tercapai. Di hari sepi jumlah nota ikut turun, sekitar 80–100. Di hari ramai bisa sampai sekitar 290.
- **Item per nota:** jumlahnya diambil dari distribusi historis.
- **Barang dan satuan:** dipilih acak sesuai bobot historis (frekuensi terjual per barang + satuan). Barang yang sama tidak muncul dua kali dalam satu nota.
- **Qty:** diambil dari qty historis barang tersebut, lalu dipotong ke stok yang tersedia.
- **Harga:** `harga_jual` dari `m_barang_satuan`, `total` = harga × qty, tanpa diskon dan pajak.
- **`harga_beli_terakhir`:** harga beli terakhir per satuan terkecil, sama dengan cara app (`getHargaBeliTerakhir`).
- **Customer:** sesuai proporsi historis untuk customer aktif (TUNAI sekitar 96%).
- **Jam transaksi:** mengikuti distribusi jam historis (ramai jam 16–21).
- **`tanggal_server` dan `tanggal_setor`:** `tanggal_server` = tanggal + 1–3 detik, `tanggal_setor` = tanggal_server − 1 hari (pola data lama).

### Stok tidak boleh minus
- Stok = total pembelian − total penjualan dalam satuan terkecil, sama seperti `v_stok_barang`. Stok awal diambil dari view tersebut saat generate.
- Barang hanya bisa dijual kalau stoknya cukup.
- Kalau sebuah nota memakai stok dari pembelian hari itu, jam nota **digeser ke setelah jam pembelian**.
- Ada pengecekan ulang di PHP (berhenti kalau stok minus) dan di SQL (rollback).

### Pembelian (setiap pagi 08:00–11:30, sebelum penjualan memakai stoknya)
Setiap hari punya jenis pembelian (konstanta `MODE_BELI`):

| Jenis hari | Kapan | Barang yang dibeli | Stok setelah beli | Nota per hari | Nilai (contoh simulasi) |
|---|---|---|---|---|---|
| **besar** (kulakan) | 1–2 hari acak per minggu, Senin–Sabtu | stok < 12 hari penjualan | 18–24 hari | 15–25 | rata-rata sekitar 7 jt (2–4x omzet hari itu) |
| **biasa** | Senin–Sabtu lainnya | stok < 9 hari penjualan | 12–16 hari | 5–11 | sekitar 1 jt |
| **minggu** | setiap Minggu | hanya yang kritis (stok < 1,5 hari) | 7–10 hari | 0–2 | sangat kecil |

- **Barang wajib:** barang dengan stok < 4 hari penjualan selalu dibeli, meskipun jumlah nota melewati batas di atas (maksimal 25/14/2 nota).
- **Laju penjualan:** nilai terbesar antara laju historis dan rata-rata bergerak hasil simulasi.
- **Supplier, satuan, dan harga:** diambil dari pembelian terakhir barang tersebut. Satuan harus aktif, supaya ikut terhitung di `v_stok_barang`.
- **Qty:** cukup sampai target stok, dibulatkan ke atas ke satuan beli.
- **Satu nota = satu supplier**, maksimal 20 item. Supplier diprioritaskan berdasarkan nilai penjualan yang terancam hilang (laju × harga × urgensi).
- **Kolom lain:** jatuh tempo +14 hari, status 1, diskon dan pajak 0.

### Biaya operasional (tidak setiap hari)
- **Hari yang ada biayanya:** peluangnya sama dengan proporsi hari yang ada biaya di histori (sekitar 80%).
- **Jumlah per hari:** dari distribusi historis.
- **Jenis biaya:** sesuai frekuensi historis.
- **Nominal dan keterangan:** pasangan nominal + keterangan yang pernah ada untuk jenis biaya tersebut.
- **Listrik (`BAA004`):** setiap tanggal 5, nominal rata-rata historis ×0,9–1,1.
- **Jam:** 08:00–21:00.

## Mengubah aturan
Semua parameter adalah konstanta di bagian atas `DummyTransaksiGenerator`:
- kode master, `STATUS_BARANG`, dan jendela acuan
- pola omzet: `FAKTOR_HARI`, `FAKTOR_GAJIAN`, `ACAK_HARIAN`, `TREN_MINGGUAN`/`BATAS_TREN`, `PELUANG_SEPI`/`PELUANG_RAMAI`, `HARI_SPESIAL` (tambahkan tanggal lain, misalnya Lebaran), dan `BATAS_BULANAN`
- pola pembelian: `MODE_BELI`, `REORDER_HARI`, dan jumlah item per nota beli
- tanggal listrik

Kalau aturannya diubah, perbarui juga file ini.

Header file SQL hasil generate memuat ringkasan per bulan (rata-rata omzet vs acuan) dan per hari (hari, omzet, deviasi, nilai beli, jenis hari beli, keterangan sepi/ramai). Ringkasan yang sama juga tampil di `dummy:generate`.
