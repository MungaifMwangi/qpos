<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_note_id',
        'lpo_item_id',
        'product_id',
        'qty_received',
        'unit_cost',
        'line_total',
    ];

    public function goodsReceiptNote()
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'goods_receipt_note_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function lpoItem()
    {
        return $this->belongsTo(\App\Models\LpoItem::class, 'lpo_item_id');
    }
}
