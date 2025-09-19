<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [AuthController::class, 'showHome'])->name('home');

//авторизация
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']);

//для проверки авторизации
Route::get('/api/check-auth', [AuthController::class, 'checkAuth']);

//защищенные роуты
Route::middleware('auth')->group(function () {
    //таблица у руководителя
    Route::get('/dashboard', function () {
        if (Auth::user()->role !== 'manager') {
            return redirect('/orders/create');
        }
        return view('dashboard');
    });

    //для оператора
    Route::get('/orders/create', function () {
        return view('orders.create');
    })->name('orders.create');

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});
