@extends('site.layout')
@section('title', 'Produto')
@section('conteudo')

    <div class="row container">
        <div class="col s12 m4">
            <div class="card cyan lighten-5">
                <div class="card-content white-text">
                    <span class="card-title black-text darken-1">{{$produto->nome}}</span>
                    <p class="black-text lighten-4">{{$produto->descricao}}</p>
                    <p class="black-text">Vendedor: {{$produto->user->name}}</p>
                    <p class="black-text">Categoria: {{$produto->categoria->nome}}</p>
                </div>
            </div>
        </div>
    </div>

@endsection