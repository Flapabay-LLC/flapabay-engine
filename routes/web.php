<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DocumentationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/documentation', [DocumentationController::class, 'index'])->name('doc');
