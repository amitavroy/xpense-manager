<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillerController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('accounts', AccountController::class)
        ->except(['edit']);

    Route::resource('transactions', TransactionController::class)
        ->except(['edit']);

    Route::resource('categories', CategoryController::class)
        ->except(['edit']);

    Route::resource('billers', BillerController::class)
        ->except(['edit']);

    Route::resource('bills', BillController::class)
        ->only(['store', 'update']);

    Route::post('bill-pay', BillPaymentController::class)
        ->name('bill-pay');

    Route::resource('incomes', IncomeController::class)
        ->except(['edit'])
        ->parameters(['incomes' => 'transaction']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
