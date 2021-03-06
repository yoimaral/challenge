<?php

use App\Http\Controllers\PaymentController;
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

Route::get('/', [PaymentController::class, 'create']);

Route::resource('payments', PaymentController::class);
Route::put('payments/retry/{payment}', [PaymentController::class, 'retry'])->name('payments.retry');
Route::get('payments/bank-list', [PaymentController::class, 'bankList'])->name('payments.bank.list');
