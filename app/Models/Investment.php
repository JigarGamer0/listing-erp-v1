<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'investor_id', 'amount', 'investment_date', 'notes', 'status', 'expense_id',
    ];

    protected $casts = [
        'investment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
