<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public $storageUrl;

    public function __construct() {
        $this->storageUrl = Storage::url('customers/');
    }
}
