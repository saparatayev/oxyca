@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Products</h1>
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

    <a href="{{ route('products.create') }}" class="btn btn-success mb-3 shadow">New product</a>

    <x-adminlte-datatable id="table1" :heads="$heads">
        @foreach($config['data'] as $product)
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
                    <a href="{{ route('products.edit', ['product' => $product]) }}" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </a>
                    <a href="#" class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                        <i class="fa fa-lg fa-fw fa-trash"></i>
                    </a>
                </nobr></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@stop

@section('css')
    
@stop

@section('js')
    
@stop
