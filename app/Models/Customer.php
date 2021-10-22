<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'fio', 'phone', 'email', 'image',
        'admin_created_id', 'admin_updated_id'
    ];
}
