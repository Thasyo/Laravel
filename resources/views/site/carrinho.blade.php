@extends('site.layout')
@section('title', 'Carrinho')
@section('conteudo')

    <h3 class="row container center">Carrinho ({{$itens->count()}})</h3>

    @if ($itens->count() == 0)
        <div class="row container center">
            <h2>Carrinho vazio!</h2>
        </div>
    @else
        <div class="row container center">
                <table class="striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($itens as $item)
                            <tr>
                                <td>{{$item->name}}</td>
                                <td>R$ {{number_format($item->price, 2,',','.')}}</td>
                                <form action="{{route('site.atualizaCarrinho')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <td><input style="width:40px; font-weight: bold;" class="white center" type="number" min="1" name="qnt" value="{{$item->quantity}}"></td>  
                                <input type="hidden" name="id" value="{{$item->id}}">
                                <td>
                                    <button type="submit" class="btn-floating waves-effect waves-light orange"><i class="material-icons">refresh</i></button>
                                </form>
                                    <form action="{{route('site.removeCarrinho')}}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        <button type="submit" class="btn-floating waves-effect waves-light red"><i class="material-icons">delete</i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row container left">
                    <h4>Total: R$ {{number_format(\Cart::getTotal(), 2, ',','.')}}</h4>
                </div>

                <div class="row container center">
                    <a href="{{route('site.index')}}" class="btn waves-effect waves-light blue">Continuar comprando<i class="material-icons">arrow_back</i></a>
                    <a href="{{route('site.limpaCarrinho')}}" class="btn waves-effect waves-light blue">Limpar carrinho<i class="material-icons">clear</i></a>
                    <a class="btn waves-effect waves-light green">Finalizar pedido<i class="material-icons">check</i></a>
                </div>
        </div>
    @endif

@endsection