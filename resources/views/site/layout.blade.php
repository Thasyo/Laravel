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
        <a href="#" class="brand-logo left">First Project</a>
        <ul id="nav-mobile" class="right">
            <li><a class='dropdown-trigger' href='#' data-target='dropdown1'>Categorias<i class="material-icons right">add</i></a></li>
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
</script>
</body>
</html>