<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_id',
        'product_id',
        'qty_ordered',
        'unit_cost',
        'total_cost',
    ];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class, 'lpo_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
