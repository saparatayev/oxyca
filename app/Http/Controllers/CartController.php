<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CartController extends AdminController
{
    public $storageUrl;

    public function __construct() {
        $this->storageUrl = Storage::url('products/');
    }

    public function superGetTotalPrice($productsInCart) {
        if($productsInCart) {
            $productsIds = array_keys($productsInCart);
            
            $products = \App\Models\Product::whereIn('id',$productsIds)->get();
            
            return $this->getTotalPrice($products,$productsInCart);
        } else return 0;
    }

    /**
     * Ajax request handler - plus the quantity of item in cart
     */
    public function add(Request $request,$id) {
        
        $id = intval($id);

        $productsInCart = $this->getProductsFromCart($request);

        if(!$productsInCart) $productsInCart = array();

        // Проверяем есть ли уже такой товар в корзине 
        if (array_key_exists($id, $productsInCart)) {
            // Если такой товар есть в корзине, но был добавлен еще раз, увеличим количество на 1
            $productsInCart[$id]++;
        } else {
            // Если нет, добавляем id нового товара в корзину с количеством 1
            $productsInCart[$id] = 1;
        }
        
        $request->session()->put('products',$productsInCart);

        $totalPrice = $this->superGetTotalPrice($productsInCart);

        $product = \App\Models\Product::find($id);
        if (!$product) {
            abort(404);
        }

        return \Response::json([
            'cartCount'=>$this->countItems($request),
            'prodByIdCount'=>$productsInCart[$id],
            'oneProdTotalPrice'=>$productsInCart[$id] * $product->price,
            'totalPrice'=>$totalPrice
        ]);

        exit();
    }

    /**
     * Because datatables don't show data-attributes in the table
     * have to add item to cart on simple get request with page reload
     */
    public function addToCartNotAjax(Request $request,$id) {
        $id = intval($id);

        $productsInCart = $this->getProductsFromCart($request);

        if(!$productsInCart) $productsInCart = array();

        // Проверяем есть ли уже такой товар в корзине 
        if (array_key_exists($id, $productsInCart)) {
            // Если такой товар есть в корзине, но был добавлен еще раз, увеличим количество на 1
            $productsInCart[$id]++;
        } else {
            // Если нет, добавляем id нового товара в корзину с количеством 1
            $productsInCart[$id] = 1;
        }
        
        $request->session()->put('products',$productsInCart);

        $totalPrice = $this->superGetTotalPrice($productsInCart);

        $product = \App\Models\Product::find($id);
        if (!$product) {
            abort(404);
        }

        return redirect()->route('cart.index');
    }

    /**
     * Ajax request handler - subtract the quantity of item in cart
     */
    public function subtract(Request $request,$id) {

        $id = intval($id);

        $productsInCart = $this->getProductsFromCart($request);

        if(!$productsInCart) $productsInCart = array();

        if (array_key_exists($id, $productsInCart)) {
            $productsInCart[$id]--;

            if($productsInCart[$id] < 1)  {
                unset($productsInCart[$id]);
                $c = 0;
            } else $c = $productsInCart[$id];
        }
        
        $request->session()->put('products',$productsInCart);

        $totalPrice = $this->superGetTotalPrice($productsInCart);

        $product = \App\Models\Product::where('id',$id)->first();
        
        return \Response::json([
            'cartCount'=>$this->countItems($request),
            'prodByIdCount'=>$c,
            'oneProdTotalPrice'=>$c * $product->price,
            'totalPrice'=>$totalPrice
        ]);

        exit();
    }

    /**
     * Ajax request handler - delete item from cart
     */
    public function delete(Request $request,$id) {
        $id = intval($id);

        $productsInCart = $this->getProductsFromCart($request);
        
        unset($productsInCart[$id]);

        $request->session()->put('products',$productsInCart);        

        $totalPrice = $this->superGetTotalPrice($productsInCart);

        return \Response::json([
            'cartCount'=>$this->countItems($request),
            'totalPrice'=>$totalPrice
        ]);
    }

    /**
     * Show the page where user created a new order
     */
    public function index(Request $request) {

        $productsInCart = $this->getProductsFromCart($request);

        if($productsInCart) {
            $productsIds = array_keys($productsInCart);
            
            $products = \App\Models\Product::whereIn('id',$productsIds)->get();
            
            $totalPrice = $this->getTotalPrice($products,$productsInCart);
        } else {
            $products = $totalPrice = null;
        }

        return view('orders.cart',compact(
            'productsInCart','products','totalPrice'
        ))->with([
            'cartCount' => $this->countItems($request),
            'storageUrl' => $this->storageUrl
        ]);
    }

    public function getProductsFromCart(Request $request) {
        $p = $request->session()->get('products','default');
        if($p != 'default') return $p;
        return false;
    }

    public function getTotalPrice($products,$productsInCart) {
        
        $total = 0;

        foreach($products as $item) {
            $total += $item->price * $productsInCart[$item->id];
        }

        return $total;
    }

    /**
     * Clear Cart
     */
    public function clear(Request $request) {
        $emptyCart = array();
        $productsInCart = $this->getProductsFromCart($request);
        if($productsInCart) $request->session()->put('products',$emptyCart);
    }

    public function checkout(Request $request) {

        $input = $request->all();
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'cart_count' => 'required|gt:0',
        ];

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customer = Customer::find($input['customer_id']);

        $productsInCart = $this->getProductsFromCart($request);

        $productsIds = array_keys($productsInCart);

        $products = \App\Models\Product::whereIn('id',$productsIds)->get();
            
        $totalPrice = number_format($this->getTotalPrice($products,$productsInCart), 2, '.', '');
        
        /**
         * Create a new Order and save it in DB.
         * Then attach products to saved Order.
         */

        // transaction begins
        DB::beginTransaction();

        try {

            $order = new \App\Models\Order();
            $order->total = $totalPrice;
            $customer->orders()->save($order); // save through relationship

            foreach($products as $p) {
                $order->products()->attach($p->id, ['quantity' => $productsInCart[$p->id]]);
            }

            $this->clear($request);


        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error','New Order adding error 500');
        }

        DB::commit();
        // transaction ends
        
        return redirect()->route('orders.index')->with([
            'status'=>'Order added'
        ]);
    }
}
