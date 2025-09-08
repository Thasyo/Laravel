@extends('site.layout')
@section('title', 'Produto')
@section('conteudo')

    <div class="row container">
        <div class="col s12 m4">
            <div class="card cyan lighten-5 p-2">
                <div class="card-content white-text">
                    <span class="card-title black-text darken-1">{{$produto->nome}}</span>
                    <p class="black-text lighten-4">{{$produto->descricao}}</p>
                    <p class="black-text">Vendedor: {{$produto->user->name}}</p>
                    <p class="black-text">Categoria: {{$produto->categoria->nome}}</p>
                    <p class="black-text">Preço: R$ {{number_format($produto->price, 2, ',', '.')}}</p>
                </div>
                <form action="{{route('site.addCarrinho')}}" method="POST" enctype="multipart/form-data" class="center p-2">
                    @csrf
                    <input type="hidden" name="id" value="{{$produto->id}}">
                    <input type="hidden" name="name" value="{{$produto->nome}}">
                    <input type="hidden" name="price" value="{{$produto->price}}">
                    <input type="number" name="qnt" min="1" value="1">
                    <button class="btn m-2" type="submit" name="action">Adicionar
                        <i class="material-icons right">add</i>
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection