@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>New Customer</h1>
@stop

@section('content')

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="post" class="row" enctype="multipart/form-data">
        @csrf
        <div class="col-6">
            <x-adminlte-input name="fio" value="{{ old('fio') }}" type="text" label="FIO" placeholder="FIO"/>
        </div>
        <div class="col-6">
            <x-adminlte-input name="phone" value="{{ old('phone') }}" type="text" label="Phone (Canada) 1 XXX XXX XXXX" placeholder="12224567896"/>
        </div>
        <div class="col-6">
            <x-adminlte-input name="email" value="{{ old('email') }}" type="email" label="Email" placeholder="Email"/>
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
