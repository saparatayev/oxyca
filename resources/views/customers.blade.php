@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Customers</h1>
@stop

@section('content')
    {{-- Setup data for datatables --}}
    @php
        $heads = [
            'ID',
            'FIO',
            'Phone',
            'Email',
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
