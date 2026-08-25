<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'title', 'category_id', 'amount', 'expense_date', 'type',
        'notes', 'receipt', 'created_by', 'include_in_calculation',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'include_in_calculation' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'amount', 'expense_date', 'type', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category() { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by'); }
}
