<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'is_active',
        'description',
    ];

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'chart_of_account_id');
    }

    public function getBalanceAttribute()
    {
        $debits = $this->journalLines()->sum('debit');
        $credits = $this->journalLines()->sum('credit');

        if ($this->normal_balance === 'debit') {
            return $debits - $credits;
        }
        return $credits - $debits;
    }
}
