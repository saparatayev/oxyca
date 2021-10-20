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
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $btnEdit = '<a href="#" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </a>';
        $btnDelete = '<a href="#" class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                        <i class="fa fa-lg fa-fw fa-trash"></i>
                    </a>';
        $config = [
            'data' => $customers,
            'order' => [[0, 'asc']],
            'columns' => [null, null, null, null, ['orderable' => false]],
        ];
    @endphp

    <x-adminlte-datatable id="table1" :heads="$heads">
        @foreach($config['data'] as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->fio }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->email }}</td>
                <td><nobr>{!! $btnEdit.$btnDelete !!}</nobr></td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

@stop

@section('css')
    
@stop

@section('js')

@stop
