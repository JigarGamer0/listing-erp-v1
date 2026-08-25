<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year', 'base_salary', 'total_commission',
        'advance_deduction', 'other_deductions', 'bonus', 'net_payable',
        'paid_amount', 'status', 'paid_date', 'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'bonus' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getMonthNameAttribute()
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    public function getRemainingAttribute()
    {
        return $this->net_payable - $this->paid_amount;
    }

    // Scopes
    public function scopePending($query) { return $query->where('status', 'pending'); }
}
