<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrdersController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('orders')->with([
            'cartCount' => $this->countItems($request),
            'orders' => Order::with(['customer', 'products'])->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['customer', 'products'])->find($id);
        if(!$order) {
            abort(404);
        }

        return view('orders.show')->with([
            'cartCount' => $this->countItems($request),
            'storageUrlProducts' => Storage::url('products/sm/'),
            'storageUrlCustomers' => Storage::url('customers/sm/'),
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::with(['customer', 'products'])->find($id);
        if(!$order) {
            abort(404);
        }
        
        DB::beginTransaction();
        
        try {
            $order->products()->detach();
        
            $order->delete();
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error','Order deleting error 500');
        }

        DB::commit();

        return redirect()->route('orders.index')->with('status', 'Deleted Order succesfully');
    }
}
