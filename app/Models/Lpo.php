<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lpo extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_number',
        'supplier_id',
        'status',
        'total_amount',
        'issued_at',
        'notes',
        'created_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(LpoItem::class, 'lpo_id');
    }

    public function goodsReceiptNotes()
    {
        return $this->hasMany(GoodsReceiptNote::class, 'lpo_id');
    }

    public function supplierInvoice()
    {
        return $this->hasOne(SupplierInvoice::class, 'lpo_id');
    }
}
