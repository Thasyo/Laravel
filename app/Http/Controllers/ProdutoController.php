<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(): View {
        $produtos = Produto::paginate(6);
        return view('site.home',compact('produtos'));
    }

    public function show(int $id = 0): string {
        $produto = Produto::find($id); // a função find({:id}) procura dentro do banco o registro com o id colocado como parâmetro.
        return dd($produto);
    }
}
