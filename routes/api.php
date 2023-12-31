<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => '/v1'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/products/filter/{page_number}/{per_page}', [ProductController::class, 'filterProducts']);
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => '/v1'], function () {
    /**
     * Product Routes
     */
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product_id}', [ProductController::class, 'showProduct']);
    Route::put('/products/{product_id}', [ProductController::class, 'update']);
    Route::patch('/products/{product_id}', [ProductController::class, 'update']);
    Route::delete('/products/{product_id}', [ProductController::class, 'destroy']);

    /**
     * Orders Route
     */
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order_id}', [OrderController::class, 'showOrder']);
    Route::get('/orders/filter/{page_number}/{per_page}', [OrderController::class, 'filterOrders']);
    Route::delete('/orders/{order_id}', [OrderController::class, 'destroy']);

});
