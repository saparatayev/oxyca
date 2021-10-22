@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>New Order</h1>
@stop

@section('content')

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('order.store') }}" method="post" class="row" enctype="multipart/form-data">
        @csrf
        <div class="col-6">
            <x-adminlte-input name="customer_id" value="{{ old('customer_id') }}" type="text" label="Customer ID" placeholder="Customer ID"/>
        </div>
        
        
        
        <x-adminlte-button class="btn-flat" type="submit" label="New Order" theme="success" icon="fas fa-lg fa-save"/>
    </form>
@stop

@section('css')
    
@stop

@section('js')

@stop
