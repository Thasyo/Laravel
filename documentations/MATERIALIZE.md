# Materialize CSS no Laravel: Estudo Avançado

Materialize CSS é um framework CSS moderno baseado no Material Design do Google, focado em responsividade e design elegante. Integrá-lo ao Laravel permite construir aplicações web com interface visual moderna e interativa combinada à robustez do backend Laravel.[^1][^2][^3]

## Métodos de Integração no Laravel

### 1. Instalação via CDN (Método Mais Simples)

A forma mais rápida é incluir os links CDN diretamente no layout Blade principal:[^2][^4]

```html
<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
```


### 2. Instalação via Pacote Laravel

Para integração avançada, utilize o pacote `laravel-materialize-css`:[^1]

**Instalação:**

```bash
composer require skydiver/laravel-materialize-css:dev-master
```

**Configuração em `config/app.php`:**

```php
'providers' => [
    Skydiver\LaravelMaterializeCSS\MaterializeCSSServiceProvider::class,
],

'aliases' => [
    'MaterializeCSS' => Skydiver\LaravelMaterializeCSS\MaterializeCSS::class,
],
```

**Publicar assets:**

```bash
php artisan vendor:publish --tag=materializecss --force
```

**Uso no Blade:**

```php
{!! MaterializeCSS::include_full() !!}
// ou separadamente
{!! MaterializeCSS::include_css() !!}
{!! MaterializeCSS::include_js() !!}
```


### 3. Instalação via NPM com Laravel Mix

Para controle total sobre o build:[^5]

```bash
npm install materialize-css@next --save-dev
```


## Sistema de Grid

O Materialize utiliza um sistema de grid de 12 colunas responsivo:[^6][^7]

### Breakpoints

- **s**: Small (≤600px)
- **m**: Medium (601px - 992px)
- **l**: Large (993px - 1200px)
- **xl**: Extra Large (≥1201px)


### Exemplo de Grid

```html
<div class="container">
    <div class="row">
        <div class="col s12 m6 l4">Conteúdo</div>
        <div class="col s12 m6 l8">Mais conteúdo</div>
    </div>
</div>
```


## Exemplos Práticos de Implementação

### Layout Principal com Navegação

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="blue darken-2">
        <div class="nav-wrapper container">
            <a href="{{ route('home') }}" class="brand-logo">Minha App</a>
            <ul class="right hide-on-med-and-down">
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('users.index') }}">Usuários</a></li>
            </ul>
            
            <ul id="nav-mobile" class="sidenav">
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('users.index') }}">Usuários</a></li>
            </ul>
            <a href="#" data-target="nav-mobile" class="sidenav-trigger">
                <i class="material-icons">menu</i>
            </a>
        </div>
    </nav>

    <main class="container" style="margin-top: 2rem;">
        @yield('content')
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });
    </script>
</body>
</html>
```


### Formulário de Cadastro com Validação Laravel

**Controller:**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ], [
            'name.required' => 'O nome é obrigatório',
            'email.unique' => 'Este email já está em uso',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
                        ->with('success', 'Usuário criado com sucesso!');
    }
}
```

**View Blade:**

```html
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col s12 m8 l6 offset-m2 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Criar Novo Usuário</span>
                
                @if ($errors->any())
                    <div class="card-panel red lighten-4 red-text text-darken-2">
                        <ul class="collection">
                            @foreach ($errors->all() as $error)
                                <li class="collection-item red-text">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">account_circle</i>
                            <input id="name" name="name" type="text" class="validate" 
                                   value="{{ old('name') }}" required>
                            <label for="name">Nome Completo</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">email</i>
                            <input id="email" name="email" type="email" class="validate" 
                                   value="{{ old('email') }}" required>
                            <label for="email">Email</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <button class="btn waves-effect waves-light blue" type="submit">
                                Criar Usuário
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```


### Dashboard com Cards e Estatísticas

```html
@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Estatísticas -->
    <div class="col s12 m6 l3">
        <div class="card-panel blue white-text center">
            <i class="material-icons large">people</i>
            <h4>{{ $totalUsers }}</h4>
            <p>Total Usuários</p>
        </div>
    </div>
    
    <div class="col s12 m6 l3">
        <div class="card-panel green white-text center">
            <i class="material-icons large">trending_up</i>
            <h4>{{ $salesTotal }}</h4>
            <p>Vendas</p>
        </div>
    </div>
</div>

<!-- Lista de Usuários Recentes -->
<div class="row">
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Últimos Usuários</span>
                <ul class="collection">
                    @foreach($recentUsers as $user)
                        <li class="collection-item avatar">
                            <i class="material-icons circle blue">person</i>
                            <span class="title">{{ $user->name }}</span>
                            <p>{{ $user->email }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
```


## Principais Classes do Materialize CSS

### Classes de Grid e Layout

```css
.container          /* Centraliza e limita largura */
.row               /* Linha do grid */
.col               /* Coluna base */
.s1 a .s12        /* Small screens */
.m1 a .m12        /* Medium screens */  
.l1 a .l12        /* Large screens */
.offset-s1 a .offset-s12  /* Offsets */
```


### Classes de Cores[^8][^9]

```css
/* Cores Base */
.red, .blue, .green, .orange, .purple, .cyan, .teal

/* Variações */
.lighten-1 a .lighten-5    /* Mais claro */
.darken-1 a .darken-4      /* Mais escuro */

/* Texto */
.red-text, .blue-text, .white-text

/* Exemplos */
.blue.darken-2             /* Azul escuro */
.green-text.text-lighten-3 /* Texto verde claro */
```


### Classes de Botões[^10][^11]

```css
.btn                /* Botão padrão elevado */
.btn-flat          /* Botão plano */
.btn-floating      /* Botão flutuante circular */
.btn-large         /* Botão grande */
.btn-small         /* Botão pequeno */
.waves-effect      /* Efeito de onda */
.disabled          /* Desabilitado */
```


### Classes de Cards[^12]

```css
.card              /* Card base */
.card-panel       /* Card simples */
.card-content     /* Conteúdo do card */
.card-title       /* Título do card */
.card-action      /* Área de ações */
.card-image       /* Imagem do card */
.hoverable        /* Efeito hover */
```


### Classes de Formulários[^13][^14]

```css
.input-field      /* Campo de entrada */
.validate         /* Validação automática */
.invalid          /* Campo inválido */
.valid            /* Campo válido */
.active           /* Label ativo */
.browser-default  /* Estilo nativo */
```


### Classes de Utilidades[^15][^16]

```css
/* Alinhamento */
.left-align, .center-align, .right-align
.valign-wrapper   /* Alinhamento vertical */

/* Visibilidade */
.hide             /* Ocultar */
.hide-on-small-only     /* Ocultar em telas pequenas */
.show-on-medium-and-up  /* Mostrar em tablet+ */

/* Formatação */
.truncate         /* Truncar texto */
.hoverable        /* Efeito hover */
```


### Classes de Componentes JavaScript[^17][^18]

```css
.modal            /* Modal */
.modal-trigger    /* Trigger do modal */
.collapsible      /* Lista sanfonada */
.carousel         /* Carrossel */
.tooltipped       /* Tooltip */
.dropdown-trigger /* Trigger dropdown */
```


## Configuração de Componentes JavaScript

### Inicialização Automática

```javascript
document.addEventListener('DOMContentLoaded', function() {
    M.AutoInit(); // Inicializa todos os componentes
});
```


### Inicialização Manual

```javascript
// Modals
var elems = document.querySelectorAll('.modal');
var instances = M.Modal.init(elems);

// Sidenav  
var elems = document.querySelectorAll('.sidenav');
var instances = M.Sidenav.init(elems);

// Select
var elems = document.querySelectorAll('select');
var instances = M.FormSelect.init(elems);
```


## Validação de Formulários

### Validação Customizada[^9][^19][^20]

```html
<div class="input-field">
    <input id="email" type="email" class="validate" required>
    <label for="email">Email</label>
    <span class="helper-text" data-error="Email inválido" data-success="Email válido"></span>
</div>
```


### Integração com Validação Laravel[^21]

```php
// Controller
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|min:2',
        'email' => 'required|email|unique:users'
    ]);
    
    // Processar dados...
}
```


## Modal de Confirmação[^17]

```html
<!-- Trigger -->
<a class="waves-effect waves-light btn modal-trigger" href="#modal1">
    Excluir
</a>

<!-- Modal -->
<div id="modal1" class="modal">
    <div class="modal-content">
        <h4>Confirmar Exclusão</h4>
        <p>Tem certeza que deseja excluir este item?</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close btn-flat">Cancelar</a>
        <a href="#!" class="modal-close btn red">Confirmar</a>
    </div>
</div>
```


## Boas Práticas

### Performance

- Use CDN para produção[^6]
- Compile apenas componentes necessários
- Minifique CSS e JS customizados
- Implemente lazy loading para componentes pesados


### Responsividade

- Sempre teste em dispositivos móveis
- Use classes de grid adequadas (`s`, `m`, `l`, `xl`)
- Implemente navegação móvel com `sidenav`
- Otimize formulários para touch


### Integração com Laravel

- Mantenha separação entre lógica do framework e apresentação
- Use validação server-side do Laravel[^21]
- Implemente mensagens flash para feedback[^22]
- Configure CSRF adequadamente em formulários

O Materialize CSS oferece uma excelente base para aplicações Laravel modernas, combinando a elegância do Material Design com a robustez do framework PHP. A integração permite criar interfaces responsivas e atrativas mantendo as melhores práticas de desenvolvimento web.

<div style="text-align: center">⁂</div>

[^1]: https://github.com/skydiver/laravel-materialize-css

[^2]: https://github.com/shakthizen/laravel-materialize

[^3]: https://www.youtube.com/watch?v=gXnJdpIbnFY

[^4]: https://pixinvent.com/materialize-material-design-admin-template/documentation/laravel-integration.html

[^5]: https://stackoverflow.com/questions/42552896/materialize-csslaravelvuejs-where-to-initialize-modals-side-nav-etc

[^6]: https://www.um.es/docencia/barzana/materializecss/buttons.html

[^7]: https://materializecss.com/getting-started.html

[^8]: https://www.scribd.com/document/431817800/A-Simple-Employee-Management-System-With-Materializecss-and-Laravel

[^9]: https://materializecss.com/buttons.html

[^10]: https://www.youtube.com/watch?v=vtLYDX_JCWQ

[^11]: https://stackoverflow.com/questions/45822665/how-to-make-materializecss-buttons-stack-nicely-on-small-screens

[^12]: https://stackoverflow.com/questions/42382172/whats-the-correct-way-to-integrate-laravel-and-materialize-css-via-sass

[^13]: https://materializeweb.com/buttons.html

[^14]: https://www.youtube.com/watch?v=ImXj4q9ZYss

[^15]: https://www.um.es/docencia/barzana/materializecss/grid.html

[^16]: https://laracasts.com/discuss/channels/laravel/including-materialize-css-framework-into-laravel

[^17]: https://materializecss.com/grid.html

[^18]: https://materializecss.com

[^19]: https://materializecss.com/text-inputs.html

[^20]: https://www.reddit.com/r/laravel/comments/6bnw55/using_materialize_css/

[^21]: https://stackoverflow.com/questions/52792066/how-to-validate-my-materializecss-form-and-if-first-value-in-select-selected

[^22]: https://stackoverflow.com/questions/41965553/how-to-integration-materialize-js-in-vue-and-laravel

[^23]: https://dev.to/kpulkit29/custom-validation-in-materialize-css-1p0e

[^24]: https://github.com/topics/materializecss-framework

[^25]: https://laravel.com/docs/12.x/validation

[^26]: https://www.youtube.com/watch?v=X088xuvYEag

[^27]: https://laracasts.com/discuss/channels/laravel/setup-materializecss-properly

[^28]: https://pixinvent.com/materialize-material-design-admin-template/documentation/text-inputs.html

[^29]: https://www.youtube.com/watch?v=xRsmI14I5-M

[^30]: https://github.com/rascoop/materialize-form

[^31]: https://laracasts.com/discuss/channels/laravel/laravel-form-submit-and-materializecss-modals

[^32]: https://stackoverflow.com/questions/tagged/materialize

[^33]: https://stackoverflow.com/questions/56884209/using-materialize-cards-conflict-with-modal-view

[^34]: https://www.tutorialspoint.com/what-are-the-different-utility-classes-in-materialize-css

[^35]: https://www.youtube.com/watch?v=_3YoFLc1I9o

[^36]: https://www.um.es/docencia/barzana/materializecss/cards.html

[^37]: https://www.geeksforgeeks.org/css/what-are-the-different-utility-classes-in-materialize-css/

[^38]: https://www.youtube.com/watch?v=qXXdH_EWZcc

[^39]: https://stackoverflow.com/questions/60889804/importing-materializecss-from-cdn-and-getting-select-dropdown-to-work-in-reactjs

[^40]: https://materializecss.com/modals.html

[^41]: https://stackoverflow.com/questions/65575536/is-there-a-way-to-specify-the-background-color-only-once-using-materialize-css

[^42]: https://www.youtube.com/watch?v=2TGfYMMkCfI

[^43]: https://pixinvent.com/materialize-material-design-admin-template/documentation/modals.html

[^44]: https://stackoverflow.com/questions/46351554/materialize-css-change-background-color

[^45]: https://materializecss.com/color.html

[^46]: https://www.youtube.com/watch?v=wX1xak66E6I

[^47]: https://www.um.es/docencia/barzana/materializecss/color.html

[^48]: https://github.com/seanmavley/Laravel-Auth-MaterializeCSS

[^49]: https://www.youtube.com/watch?v=sLFNVXY0APk

[^50]: https://stackoverflow.com/questions/39313758/materialize-css-custom-form-validation-error-message

[^51]: https://laravel.io/forum/style-not-loading-via-route-controller

[^52]: https://www.youtube.com/watch?v=PDffvSBwd3o

[^53]: https://www.jlgregorio.com.br/2022/09/06/introducao-ao-laravel-framework-parte-08-crud/

[^54]: https://gist.github.com/dogrocker/20c255a7919d7033f73d

[^55]: https://www.youtube.com/watch?v=TsmagMpNVAc

[^56]: https://github.com/skydiver/laravel-materialize-css/blob/master/README.md

[^57]: https://www.youtube.com/playlist?list=PLS1QulWo1RIagiNF9X4B_mkJYzjCbx6GI

[^58]: https://www.mindluster.com/lesson/25246-video

[^59]: https://stackoverflow.com/questions/tagged/materialize?tab=newest\&page=5

[^60]: https://materializecss.com/helpers.html

[^61]: https://ppl-ai-code-interpreter-files.s3.amazonaws.com/web/direct-files/f03a69071d253d0b68b932c2776d8d4b/47706402-fa93-499a-b085-565fe9326703/3794fa0c.md

