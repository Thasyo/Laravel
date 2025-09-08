<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
     <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <!-- Dropdown Structure -->
    <ul id='dropdown1' class='dropdown-content'>
        @foreach ($categoriasMenu as $categoria) 
            <li><a href="{{route('site.categoria', $categoria->id)}}">{{$categoria->nome}}</a></li>
        @endforeach
    </ul>
    <nav>
        <div class="nav-wrapper container">
        <a href="{{route('site.index')}}" class="brand-logo left">First Project</a>
        <ul id="nav-mobile" class="right">
            <li><a class='dropdown-trigger' href='#' data-target='dropdown1'>Categorias<i class="material-icons right">add</i></a></li>
            <li><a href='{{route('site.carrinho')}}'>Carrinho <span class="new badge blue" data-badge-caption="">{{\Cart::getContent()->count()}}</span></a></li>
        </ul>
        </div>
    </nav>

    @yield('conteudo')

<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    const elemDrop = document.querySelectorAll('.dropdown-trigger');
    const instanceDrop = M.Dropdown.init(elemDrop, {
            coverTrigger: false,
            constrainWidth: false 
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            M.toast({html: "{{ session('success') }}", classes: 'green darken-1 rounded'});
        @endif

        @if(session('error'))
            M.toast({html: "{{ session('error') }}", classes: 'red darken-1 rounded'});
        @endif

        @if(session('warning'))
            M.toast({html: "{{ session('warning') }}", classes: 'orange darken-1 rounded'});
        @endif
    });
</script>
</body>
</html>