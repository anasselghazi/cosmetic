<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'quantity', 'total_price', 'status'
    ];

    public function product()
    {
    return $this->belongsTo(Product::class);
    }

    public function user()
    {
    return $this->belongsTo(user::class);
    }

}
