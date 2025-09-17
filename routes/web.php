<?php

use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'index'])->name('site.index');

Route::get('/produto/{id}', [SiteController::class, 'details'])->name('site.details');

Route::get('/produtos/categoria/{id}', [SiteController::class, 'categoria'])->name('site.categoria');

// Carrinho
Route::get('/carrinho', [CarrinhoController::class, 'carrinhoLista'])->name('site.carrinho');
Route::post('/carrinho', [CarrinhoController::class, 'adicionaCarrinho'])->name('site.addCarrinho');
Route::post('/remover-carrinho', [CarrinhoController::class, 'removeCarrinho'])->name('site.removeCarrinho');
Route::post('/atualizar-carrinho', [CarrinhoController::class, 'atualizaCarrinho'])->name('site.atualizaCarrinho');
Route::get('/limpar-carrinho', [CarrinhoController::class, 'limpaCarrinho'])->name('site.limpaCarrinho');

// Login
Route::view('/login', 'login.form')->name('login.form');
Route::post('/login', [LoginController::class, 'auth'])->name('login.auth');
Route::get('/logout', [LoginController::class, 'logout'])->name('login.logout');

//dashboard (admin)
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');