<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Produto;

class SiteController extends Controller
{
    public function index(): View {
        $produtos = Produto::paginate(6);
        return view('site.home',compact('produtos'));
    }

    public function details($id): View {
        $produto = Produto::where('id', $id)->first();
        return view('site.details', compact('produto'));
    }

    public function categoria($id): View {
        $produtos = Produto::where('id_categoria', $id)->paginate(3);
        return view('site.categoria', compact('produtos'));
    }
}
