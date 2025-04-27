<?php

use App\Http\Controllers\controllerBarang;
use App\Http\Controllers\controllerPenjualan;
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
