<?php

use App\Http\Controllers\controllerAkun;
use App\Http\Controllers\controllerBarang;
use App\Http\Controllers\controllerBarangSatuan;
use App\Http\Controllers\controllerCustomer;
use App\Http\Controllers\controllerJabatan;
use App\Http\Controllers\controllerPegawai;
use App\Http\Controllers\controllerPenjualan;
use App\Http\Controllers\controllerSatuan;
use App\Http\Controllers\controllerSupplier;
use App\Http\Controllers\viewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [viewController::class,'Dashboard']);

// abaikan
Route::get('dashboard', [viewController::class,'Dashboard']);
Route::get('pages', [viewController::class,'Pages']);

Route::post('/send-message', [viewController::class, 'sendMessage'])->name('send.message');


// penjualan
Route::get('/penjualan', [controllerPenjualan::class,'viewPenjualan'])->name('index.penjualan');
Route::get('/products-list', [controllerPenjualan::class,'getBarangSatuan']);
Route::post('/input-penjualan', [controllerPenjualan::class, 'inputPenjualan'])->name('input.penjualan');
Route::post('/edit-penjualan', [controllerPenjualan::class, 'editPenjualan'])->name('edit.penjualan');
Route::get('/detail-penjualan', [controllerPenjualan::class,'getDetailPenjualan']);

// m_barang
Route::get('/barang', [controllerBarang::class,'viewMasterBarang'])->name('index.master.barang');
Route::post('/input-master-barang', [controllerBarang::class, 'inputBarang'])->name('input.master.barang');
Route::post('/edit-master-barang', [controllerBarang::class, 'editBarang'])->name('edit.master.barang');
Route::post('/hapus-master-barang', [controllerBarang::class, 'hapusBarang'])->name('hapus.master.barang');

// m_satuan
Route::get('/satuan', [controllerSatuan::class,'viewMasterSatuan'])->name('index.master.satuan');
Route::post('/input-master-satuan', [controllerSatuan::class, 'inputSatuan'])->name('input.master.satuan');
Route::post('/edit-master-satuan', [controllerSatuan::class, 'editSatuan'])->name('edit.master.satuan');
Route::post('/hapus-master-satuan', [controllerSatuan::class, 'hapusSatuan'])->name('hapus.master.satuan');

// m_barang_satuan
Route::get('/barang-satuan', [controllerBarangSatuan::class,'viewMasterBarangSatuan'])->name('index.master.barang.satuan');
Route::post('/input-master-barang-satuan', [controllerBarangSatuan::class, 'inputBarangSatuan'])->name('input.master.barang.satuan');
Route::get('/get-jabatan-barang-satuan', [controllerBarangSatuan::class, 'getBarangSatuanEdit'])->name('edit.master.barang.satuan.data');
Route::post('/edit-master-barang-satuan', [controllerBarangSatuan::class, 'editBarangSatuan'])->name('edit.master.barang.satuan');
Route::post('/hapus-master-barang-satuan', [controllerBarangSatuan::class, 'hapusBarangSatuan'])->name('hapus.master.barang.satuan');

// m_customer
Route::get('/customer', [controllerCustomer::class,'viewMasterCustomer'])->name('index.master.customer');
Route::post('/input-master-customer', [controllerCustomer::class, 'inputCustomer'])->name('input.master.customer');
Route::post('/edit-master-customer', [controllerCustomer::class, 'editCustomer'])->name('edit.master.customer');
Route::post('/hapus-master-customer', [controllerCustomer::class, 'hapusCustomer'])->name('hapus.master.customer');

// m_supplier
Route::get('/supplier', [controllerSupplier::class,'viewMasterSupplier'])->name('index.master.supplier');
Route::post('/input-master-supplier', [controllerSupplier::class, 'inputSupplier'])->name('input.master.supplier');
Route::post('/edit-master-supplier', [controllerSupplier::class, 'editSupplier'])->name('edit.master.supplier');
Route::post('/hapus-master-supplier', [controllerSupplier::class, 'hapusSupplier'])->name('hapus.master.supplier');

// m_pegawai
Route::get('/pegawai', [controllerPegawai::class,'viewMasterPegawai'])->name('index.master.pegawai');
Route::post('/input-master-pegawai', [controllerPegawai::class, 'inputPegawai'])->name('input.master.pegawai');
Route::get('/get-jabatan-pegawai', [controllerPegawai::class, 'getJabatanPegawai'])->name('edit.master.pegawai.jabatan');
Route::post('/edit-master-pegawai', [controllerPegawai::class, 'editPegawai'])->name('edit.master.pegawai');
Route::post('/hapus-master-pegawai', [controllerPegawai::class, 'hapusPegawai'])->name('hapus.master.pegawai');

// m_jabatan
Route::get('/jabatan', [controllerJabatan::class,'viewMasterJabatan'])->name('index.master.jabatan');
Route::post('/input-master-jabatan', [controllerJabatan::class, 'inputJabatan'])->name('input.master.jabatan');
Route::post('/edit-master-jabatan', [controllerJabatan::class, 'editJabatan'])->name('edit.master.jabatan');
Route::post('/hapus-master-jabatan', [controllerJabatan::class, 'hapusJabatan'])->name('hapus.master.jabatan');

// m_akun
Route::get('/akun', [controllerAkun::class,'viewMasterAkun'])->name('index.master.akun');
Route::post('/input-master-akun', [controllerAkun::class, 'inputAkun'])->name('input.master.akun');
Route::post('/edit-master-akun', [controllerAkun::class, 'editAkun'])->name('edit.master.akun');
Route::post('/hapus-master-akun', [controllerAkun::class, 'hapusAkun'])->name('hapus.master.akun');