<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/empresa', function(){
    return view('site/empresa'); // Veremos formas melhores de fazer a mesma coisa na parte de Roteamento com redirect e view.
});

Route::any('any', function(){
    return "Permite todo tipo de acesso http (put, delete, post, get...)";
});

Route::match(['post', 'delete'], 'match', function(){
    return "Permite apenas acessos http definidos (post, delete)";
});

// Roteamento com parâmetros obrigatórios e opcionais.
Route::get('/produto/{id}', function($id){
    return "O id do produto é: " . $id;
});

Route::get('/produto/{id}/comentario/{msg?}', function($id, $msg = ''){
    return "O id do produto é: " . $id . "<br/> A categoria é: " . $msg;
});

// Roteamento com 'redirect' e 'view'.
Route::redirect('/sobre', '/empresa');

Route::view('/empresa', 'site.empresa');

//Rotas nomeadas
Route::get('/news', function(){
    return view('news');
})->name('noticias');

Route::get('/novidades', function(){
    return redirect()->route('noticias');
});

// Grupo de Rotas.
Route::group([
    'prefix' => 'admin',
    'as' => 'admin.'
], function(){

    Route::get('dashboard', function(){
        return 'Dashboard';
    })->name('dashboard');

    Route::get('client', function(){
        return 'Client';
    })->name('client');

    Route::get('master', function(){
        return 'Master';
    })->name('master');

});


//CONTROLLERS

Route::get('/produtos', [ProdutoController::class, 'index']);

//CONTROLLERS COM PARÂMETROS

Route::get('/produtos/{id?}', [ProdutoController::class, 'show']);

//CONTROLLERS RESOURCE: Controllers que já vem com uma estrutura construída para funcionalidades CRUD.
// Esse controller resource é criado com o seguinte comando: php artisan make:controller NomeController --resource
// Esse tipo de controller é ótimo para trabalhar com a manipulação de dados.

Route::resource('/clients', ClientController::class);