<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CarrinhoController extends Controller
{
    public function carrinhoLista(): View {
        $itens = Cart::getContent();
        return view('site.carrinho', compact('itens'));
    }

    public function adicionaCarrinho(Request $request): RedirectResponse {
        try{
            Cart::add([
                'id' => $request->id,
                'name' => $request->name,
                'price' => $request->price,
                'quantity' => abs($request->qnt)
            ]);

            return redirect()->route('site.carrinho')->with('success', 'Adicionado ao carrinho com sucesso!');
        }catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao adicionar ao carrinho!');
        }
    }

    public function removeCarrinho(Request $request): RedirectResponse {
        try {
            Cart::remove($request->id);
            return redirect()->back()->with('success', 'Produto removido com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao remover produto do carrinho');
        }
    }

    public function atualizaCarrinho(Request $request): RedirectResponse {
        try {
            Cart::update($request->id, [
                'quantity' => [
                    'relative' => false,
                    'value' => abs($request->qnt)
                ]
            ]);
            return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar produto!');
        }
    }

    public function limpaCarrinho(): RedirectResponse{
        try {
            Cart::clear();
            return redirect()->back()->with('warning', 'Carrinho vazio!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ocorreu algum problema ao tentar esvaziar o carrinho!');
        }
    }
}
