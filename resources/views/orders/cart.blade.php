@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>New Order</h1>
    <h3>Cart (<span id="cart-count">{{ $cartCount }}</span>)</h3>
@stop

@section('content')

    {{-- Catch block 500 Server Error --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation errors - 'cart_count' field is hidden --}}
    @if(count($errors))
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout') }}" method="post" class="row" enctype="multipart/form-data">
        @csrf
        <div class="col-6">
            <x-adminlte-input name="customer_id" value="{{ old('customer_id') }}" type="text" label="customer_id" placeholder="customer_id"/>
        </div>
        
        <input type="hidden" name="cart_count" value="{{ $cartCount }}">
        
        <div class="table-responsive cart_info">
            <table class="table table-condensed">
                <thead>
                    <tr class="cart_menu">
                        <td class="image">@lang('site.product')</td>
                        <td class="price">@lang('site.price')</td>
                        <td class="quantity">@lang('site.quantity')</td>
                        <td class="total">@lang('site.total')</td>
                        <td></td>
                    </tr>
                </thead>
                @if($productsInCart)
                <tbody>

                @foreach($products as $prod)
                    <tr>
                        <td class="cart_product">
                            @if($prod->image)
                            <img src="{{ $storageUrl . 'sm/' .  $prod->image }}">
                            @endif
                        </td>
                        <td class="cart_description">
                            <h4>{{ $prod->title }}</h4>
                            <p>Web ID: {{ $prod->sku }}</p>
                        </td>
                        <td class="cart_price">
                            <p>${{ $prod->price }}</p>
                        </td>
                        <td class="cart_quantity">
                            <div class="cart_quantity_button">
                                <a class="cart_quantity_up" data-plus="plus" data-id="{{ $prod->id }}" href="#"> + </a>
                                <input class="cart_quantity_input" type="text" name="quantity" value="{{ $productsInCart[$prod->id] }}" autocomplete="off" size="2" disabled>
                                <a class="cart_quantity_down" data-id="{{ $prod->id }}" href="#"> - </a>
                            </div>
                        </td>
                        <td class="cart_total">
                            <p class="cart_total_price">${{ $productsInCart[$prod->id] * $prod->price }}</p>
                        </td>
                        <td class="cart_delete">
                            <a class="cart_quantity_delete" data-id="{{$prod->id}}" href="#"><i class="fa fa-times"></i></a>
                        </td>
                    </tr>
                @endforeach
                    <tr>
                        <td class="cart_total" colspan="5">
                            <p class="cart_total_price">@lang('site.total_cost'): $<span id="totalPrice">{{ $totalPrice }}</span></p>
                        </td>
                    </tr>
                </tbody>
                @endif
            </table>
        </div>
        
        <x-adminlte-button class="btn-flat" type="submit" label="New Order" theme="success" icon="fas fa-lg fa-save"/>
    </form>
@stop

@section('css')
    
@stop

@section('js')
    <script src="/js/addToCart.js"></script>
    <script src="/js/deleteFromCart.js"></script>
    <script src="/js/subtractFromCart.js"></script>
@stop
