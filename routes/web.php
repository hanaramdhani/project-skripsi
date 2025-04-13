<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('dashboard', [viewController::class,'Dashboard']);
Route::get('pages', [viewController::class,'Pages']);

Route::post('/send-message', [viewController::class, 'sendMessage'])->name('send.message');


