<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Models\Product;
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



Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('products', ProductsController::class);
    Route::resource('customers', CustomersController::class);
    Route::resource('orders', OrdersController::class);

    Route::get('/cart',[CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/add-not-ajax/{id}/',[CartController::class, 'addToCartNotAjax'])->name('cart.not_ajax.add');
    Route::get('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart/delete/{id}',[CartController::class, 'delete']);
    Route::get('/cart/subtract/{id}',[CartController::class, 'subtract']);
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
});

require __DIR__.'/auth.php';
