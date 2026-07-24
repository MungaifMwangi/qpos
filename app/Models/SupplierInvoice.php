<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_id',
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'invoice_amount',
        'status',
        'notes',
    ];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class, 'lpo_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
