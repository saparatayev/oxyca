@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Orders</h1>
    <a href="{{ route('cart.index') }}">Cart (<span id="cart-count">{{ $cartCount }}</span>)</a>
@stop

@section('content')
{{-- Setup data for datatables --}}
    @php
        $heads = [
            'ID',
            'Customer',
            'Total',
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $config = [
            'data' => $orders,
        ];
    @endphp

    {{-- Success message --}}
    @if(session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    <x-adminlte-datatable id="table1" :heads="$heads">
        @foreach($config['data'] as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td><a href="{{ route('customers.edit', ['customer' => $order->customer]) }}">{{ $order->customer->fio }}</a></td>
                <td>{{ $order->total }}</td>
                <td><nobr>
                    <a href="{{ route('orders.show', ['order' => $order]) }}" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Show order">
                        <i class="fa fa-lg fa-fw fa-eye"></i>
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
