<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return view('welcome');
// })->middleware(['verify.shopify'])->name('home');


Route::get('/homeeee', function () {
    return view('welcome');
})->middleware(['verify.shopify'])->name('home');

