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

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
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
                    <form action="{{ route('orders.destroy', ['order' => $order]) }}" method="post" class="d-inline-block">
                        @csrf
                        {{ method_field('DELETE') }}
                        <button class="btn btn-xs btn-default text-danger mx-1 shadow" type="submit">
                            <i class="fa fa-lg fa-fw fa-trash"></i>
                        </button>
                    </form>
                </nobr></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
@stop

@section('css')
    
@stop

@section('js')
    
@stop
