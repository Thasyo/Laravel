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
            ], [
                'email.email' => 'Email digitado não é válido!',
                'email.required' => 'Email é obrigatório!',
                'password.required' => 'A senha é obrigatória!'
            ]);

            if(Auth::attempt($credenciais)){
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }else{
                return redirect()->back()->with('error', 'Não existe usuário com essas credenciais!');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'E-mail ou senha inválidos!');
        }
    }

    public function logout(Request $request) {
        try{
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect(route('site.index'));
        }catch(\Throwable $e){
            return redirect()->back()->with('error', 'Não foi possível sair da sua conta!');
        }
    }
}
