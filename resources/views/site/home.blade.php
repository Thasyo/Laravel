@extends('site.layout')
@section('title', 'HOME')
@section('conteudo')

    <div class="row container">
        @foreach ($produtos as $item)
            <div class="col s12 m4">
                <div class="card cyan lighten-5">
                    <div class="card-content white-text">
                    <span class="card-title black-text darken-1">{{$item->nome}}</span>
                    <p class="truncate black-text lighten-4">{{$item->descricao}}</p>
                    </div>
                    <div class="card-action">
                    <a href="#">Mais sobre</a>
                    <a href="#">Editar</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row container center">
         {{$produtos->links('custom.pagination')}} {{-- Facilita o processo de paginação --}}
    </div>

@endsection