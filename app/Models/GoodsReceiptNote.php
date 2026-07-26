<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'lpo_id',
        'purchase_id',
        'supplier_id',
        'received_date',
        'received_by',
        'notes',
    ];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class, 'lpo_id');
    }

    public function purchase()
    {
        return $this->belongsTo(\App\Models\Purchase::class, 'purchase_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'goods_receipt_note_id');
    }
}
