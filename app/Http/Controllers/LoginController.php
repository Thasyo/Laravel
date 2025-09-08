<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function auth(Request $request): RedirectResponse {
        try {
            $credenciais = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required']
            ]);

            if(Auth::attempt($credenciais)){
                $request->session()->regenerate();
                return redirect()->intended('site.index');
            }else{
                return redirect()->back()->with('error', 'Processo de Login Falhou!');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'E-mail ou senha inválidos!');
        }
    }
}
