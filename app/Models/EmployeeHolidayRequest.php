<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeHolidayRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'start_date', 'end_date', 'reason', 'status', 
        'rejection_reason', 'action_by', 'action_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
