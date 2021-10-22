@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Customers</h1>
    <a href="{{ route('cart.index') }}">Cart (<span id="cart-count">{{ $cartCount }}</span>)</a>
@stop

@section('content')
    {{-- Setup data for datatables --}}
    @php
        $heads = [
            'ID',
            'FIO',
            'Phone',
            'Email',
            'Purchases',
            'Purchases sum',
            'Photo',
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $config = [
            'data' => $customers,
            
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

    <a href="{{ route('customers.create') }}" class="btn btn-success mb-3 shadow">New customer</a>

    <x-adminlte-datatable id="table1" :heads="$heads">
        @foreach($config['data'] as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->fio }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->orders_count }}</td>
                <td>{{ $customer->orders_sum_total }}</td>
                <td>
                    @if($customer->image)
                        <img src="{{ $storageUrl . 'sm/' .  $customer->image }}" alt="">
                    @else
                        No image
                    @endif
                </td>
                <td><nobr>
                    <a href="{{ route('customers.edit', ['customer' => $customer]) }}" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </a>
                    <form action="{{ route('customers.destroy', ['customer' => $customer]) }}" method="post" class="d-inline-block">
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
