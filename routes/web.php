<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('site.index');

Route::get('/produto/{id}', [SiteController::class, 'details'])->name('site.details');

Route::get('/produtos/categoria/{id}', [SiteController::class, 'categoria'])->name('site.categoria');