<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public $storageUrl;
    protected $cartCount;

    public function __construct() {
        $this->storageUrl = Storage::url('customers/');
    }

    /**
     * Count items in cart
     */
    public static function countItems(Request $request) {
        $p = $request->session()->get('products','default');
        if($p != 'default') {
            $count = 0;
            foreach($p as $id => $quantity) {
                $count = $count + $quantity;
            }
            return $count;
        } else {
            return 0;
        }
    }
}
