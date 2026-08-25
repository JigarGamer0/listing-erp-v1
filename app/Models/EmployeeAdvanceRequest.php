<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAdvanceRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'amount', 'notes', 'status', 'rejection_reason', 'action_by', 'action_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'action_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
