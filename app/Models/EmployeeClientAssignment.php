<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeClientAssignment extends Model
{
    protected $fillable = [
        'employee_id', 'client_id', 'assigned_date', 'unassigned_date', 'status',
        'commission_type', 'commission_value', 'gst_count', 'gst_platform', 'custom_package_amount',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'unassigned_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function client() { return $this->belongsTo(Client::class); }
}
