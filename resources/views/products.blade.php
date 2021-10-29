@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Products</h1>
    <a href="{{ route('cart.index') }}">Cart (<span id="cart-count">{{ $cartCount }}</span>)</a>
@stop

@section('content')
    {{-- Setup data for datatables --}}
    @php
        $heads = [
            'ID',
            'Title',
            'SKU',
            'Price',
            'Photo',
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $config = [
            'data' => $products,
            'order' => [[0, 'asc']],
            'columns' => [null, null, null, null, ['orderable' => false]],
        ];
    @endphp
    
    {{-- Success message --}}
    @if(session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <a href="{{ route('products.create') }}" class="btn btn-success mb-3 shadow">New product</a>

    <x-adminlte-datatable id="table1" :heads="$heads">
        @foreach($config['data'] as $product)
        @can('view', $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->title }}</td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->price }}</td>
                <td>
                    @if($product->image)
                        <img src="{{ $storageUrl . 'sm/' .  $product->image }}" alt="">
                    @else
                        No image
                    @endif
                </td>
                <td><nobr>
                    <a href="{{ route('cart.not_ajax.add', ['id' => $product->id]) }}" class="btn btn-xs btn-default text-primary mx-1 shadow " title="add-to-cart">
                        <i class="fa fa-lg fa-fw fa-shopping-cart"></i>
                    </a>
                    <a href="{{ route('products.edit', ['product' => $product]) }}" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </a>
                    <form action="{{ route('products.destroy', ['product' => $product]) }}" method="post" class="d-inline-block">
                        @csrf
                        {{ method_field('DELETE') }}
                        <button class="btn btn-xs btn-default text-danger mx-1 shadow" type="submit">
                            <i class="fa fa-lg fa-fw fa-trash"></i>
                        </button>
                    </form>
                </nobr></td>
            </tr>
        @endcan
        @endforeach
    </x-adminlte-datatable>
@stop

@section('css')
    
@stop

@section('js')
    <script src="/js/addToCart.js"></script>
@stop
