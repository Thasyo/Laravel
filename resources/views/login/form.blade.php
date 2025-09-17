<form action="{{route('login.auth')}}" method="POST">
    @csrf
    Email: <br> <input name="email"> <br>
    Senha: <br> <input type="password" name="password"> <br>
    <button type="submit"> Entrar </button> <br>
    @if ($mensagem = Session::get('error'))
        {{$mensagem}}
    @endif
</form>