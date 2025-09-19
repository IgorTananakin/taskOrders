<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user/current', function () {
    if (Auth::check()) {
        return response()->json(Auth::user());
    }
    return response()->json(['error' => 'Not authenticated'], 401);
});

//поиск
Route::get('/customers', [CustomerController::class, 'searchByPhone']);

//получение списка заказов с фильтрацией по статусу
Route::get('/orders', [OrderController::class, 'index']);

//получение статистики по заказам
Route::get('/orders/statistics', [OrderController::class, 'statistics']);

//создание нового заказа
Route::post('/orders', [OrderController::class, 'store']);