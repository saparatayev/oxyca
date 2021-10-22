@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Order ID: {{ $order->id }}</h1>
    <a href="{{ route('cart.index') }}">Cart (<span id="cart-count">{{ $cartCount }}</span>)</a>
@stop

@section('content')
    <div class="">
        <div class="table-responsive cart_info">
            <table class="table table-condensed">
                <thead>
                    <tr class="cart_menu">
                        <td class="image">Product</td>
                        <td class="price">Price</td>
                        <td class="quantity">Quantity</td>
                        <td class="total">Total</td>
                        <td></td>
                    </tr>
                </thead>
                @if($order->products)
                <tbody>

                @foreach($order->products as $prod)
                    <tr>
                        <td class="cart_product">
                            @if($prod->image)
                            <img src="{{ $storageUrlProducts . $prod->image }}">
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
                                <input class="cart_quantity_input" type="text" name="quantity" value="{{ $prod->pivot->quantity }}" autocomplete="off" size="2" disabled>
                            </div>
                        </td>
                        <td class="cart_total">
                            <p class="cart_total_price">${{ $prod->pivot->quantity * $prod->price }}</p>
                        </td>
                    </tr>
                @endforeach
                    <tr>
                        <td class="cart_total" colspan="5">
                            <p class="cart_total_price">@lang('site.total_cost'): $<span>{{ $order->total }}</span></p>
                        </td>
                    </tr>
                </tbody>
                @endif
            </table>
        </div>
        <h1>Customer</h1>
        <div class="table-responsive cart_info">
            <table class="table table-condensed">
                <thead>
                    <tr class="cart_menu">
                        <td class="image">FIO</td>
                        <td class="price">Phone</td>
                        <td class="quantity">Email</td>
                        <td class="total">Photo</td>
                    </tr>
                </thead>
                @if($order->customer)
                <tbody>
                    <tr>
                        <td class="cart_description">
                            <h4>{{ $order->customer->fio }}</h4>
                        </td>
                        <td class="cart_price">
                            <p>${{ $order->customer->phone }}</p>
                        </td>
                        <td class="cart_price">
                            <p>${{ $order->customer->email }}</p>
                        </td>
                        <td class="cart_product">
                            @if($order->customer->image)
                            <img src="{{ $storageUrlCustomers . $order->customer->image }}">
                            @endif
                        </td>
                    </tr>
                </tbody>
                @endif
            </table>
        </div>
    </div>
@stop

@section('css')
    
@stop

@section('js')

@stop
