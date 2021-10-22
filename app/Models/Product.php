<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'price', 'sku', 'image',
        'admin_created_id', 'admin_updated_id'
    ];

    public function orders()
    {
        return $this->belongsToMany('App\Models\Order','orders_products');
    }
}
