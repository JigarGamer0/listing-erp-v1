<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryDeduction extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year', 'amount', 'reason', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
