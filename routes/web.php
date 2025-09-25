<?php

use App\Core\Route;
use App\Controllers\HomeController;

// Route::get('/', 'HomeController@index')->name('home');
Route::get('/', [HomeController::class, 'index'])->name('home');