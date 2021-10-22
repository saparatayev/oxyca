@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>New Product</h1>
@stop

@section('content')

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="post" class="row" enctype="multipart/form-data">
        @csrf
        <div class="col-6">
            <x-adminlte-input name="title" value="{{ old('title') }}" type="text" label="Title" placeholder="Title"/>
        </div>
        <div class="col-6">
            <x-adminlte-input name="sku" value="{{ old('sku') }}" type="text" label="SKU" placeholder="SKU"/>
        </div>
        <div class="col-6">
            <x-adminlte-input name="price" value="{{ old('price') }}" type="number" step="0.01" label="Price" placeholder="Price"/>
        </div>
        <div class="col-6">
            <x-adminlte-input name="image" type="file" label="Photo" placeholder="Photo"/>
        </div>
        
        <x-adminlte-button class="btn-flat" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
    </form>
@stop

@section('css')
    
@stop

@section('js')

@stop
