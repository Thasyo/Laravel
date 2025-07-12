<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(): string {
        return 'Index';
    }

    public function show(int $id = 0): string {
        return "Show: " . $id;
    }
}
