<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCommission extends Model
{
    protected $fillable = [
        'employee_id', 'client_id', 'billing_cycle_id', 'month', 'year',
        'package_amount', 'commission_type', 'commission_value',
        'calculated_amount', 'status', 'paid_date',
    ];

    protected $casts = [
        'package_amount' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function billingCycle() { return $this->belongsTo(ClientBillingCycle::class, 'billing_cycle_id'); }
}
