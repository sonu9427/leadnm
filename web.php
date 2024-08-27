<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/product-import',[ProductController::class,'index'])->name('products.import');
Route::post('/start-import',[ProductController::class,'import'])->name('products.start-import');

Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');

