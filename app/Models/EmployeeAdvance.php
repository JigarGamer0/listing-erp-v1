<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAdvance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'amount', 'advance_date', 'deducted', 'remaining',
        'notes', 'approved_by', 'status',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'deducted' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function approvedByUser() { return $this->belongsTo(User::class, 'approved_by'); }
}
