<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::get('/{employee}', [EmployeeController::class, 'show']);
});

Route::prefix('wallets')->group(function () {
    Route::get('/', [WalletController::class, 'index']);
    Route::post('/', [WalletController::class, 'store']);
    Route::get('/{wallet}', [WalletController::class, 'show']);
    Route::get('/{wallet}/transactions', [WalletController::class, 'transactions']);
});

Route::post('/transfers', [TransferController::class, 'store']);
Route::post('/withdrawals', [WithdrawalController::class, 'store']);
Route::post('/payroll/events', [PayrollController::class, 'store']);

Route::get('/health', HealthController::class);
