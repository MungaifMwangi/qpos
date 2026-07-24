<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'email', 'credit_limit', 'credit_terms_days'];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
