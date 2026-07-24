<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Existing relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ADD THIS NEW RELATIONSHIP
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected $appends = ['discounted_price'];

    public function getDiscountedPriceAttribute()
    {
        return number_format(($this->total / $this->quantity), 2); 
    }
}