<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(): string {
        $produtos = Produto::all();
        return dd($produtos); // a função "dd()" é uma junção do vardump() com o die()
    }

    public function show(int $id = 0): string {
        $produto = Produto::find($id); // a função find({:id}) procura dentro do banco o registro com o id colocado como parâmetro.
        return dd($produto);
    }
}
