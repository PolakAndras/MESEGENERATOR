<?php

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

// Nyilvános oldalak (App Flow 6.3). Route::view, mert nincs mögöttük logika, így kontroller sem kell.
// Public pages (App Flow 6.3). Route::view because there is no logic behind them, so no controller is needed.
Route::view('/', 'welcome')->name('home');
Route::view('/privacy', 'privacy')->name('privacy');
