<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id', 'name', 'phone', 'joining_date', 'role_title',
        'salary_type', 'fixed_salary', 'commission_type', 'commission_value', 'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'fixed_salary' => 'decimal:2',
        'commission_value' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'salary_type', 'fixed_salary', 'commission_type', 'commission_value', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function user() { return $this->belongsTo(User::class); }

    public function clientAssignments()
    {
        return $this->hasMany(EmployeeClientAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(EmployeeClientAssignment::class)->where('status', 'active');
    }

    public function commissions()
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class)->orderByDesc('year')->orderByDesc('month');
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class)->orderByDesc('advance_date');
    }

    public function pendingAdvances()
    {
        return $this->hasMany(EmployeeAdvance::class)->whereIn('status', ['active', 'partially_deducted']);
    }

    public function salaryDeductions()
    {
        return $this->hasMany(EmployeeSalaryDeduction::class);
    }

    // Computed
    public function getTotalClientsAttribute()
    {
        return $this->activeAssignments()->count();
    }

    public function getTotalPendingCommissionAttribute()
    {
        $sum = 0;
        foreach ($this->activeAssignments as $assignment) {
            $client = $assignment->client;
            if ($client && $client->status === 'active') {
                $sum += $this->calculateCommissionForClient($client);
            }
        }
        return $sum;
    }

    public function getTotalPaidCommissionAttribute()
    {
        return $this->commissions()->where('status', 'paid')->sum('calculated_amount');
    }

    public function getTotalPendingAdvanceAttribute()
    {
        return $this->pendingAdvances()->sum('remaining');
    }

    public function getPendingSalaryAttribute()
    {
        return max(0, $this->total_salary_estimate - $this->total_pending_advance);
    }

    public function getTotalSalaryEstimateAttribute()
    {
        $salary = 0;
        if (in_array($this->salary_type, ['fixed', 'both'])) {
            $salary += $this->fixed_salary;
        }
        if (in_array($this->salary_type, ['package_based', 'both'])) {
            $salary += $this->total_pending_commission;
        }
        return $salary;
    }

    public function calculateCommissionForClient(Client $client)
    {
        $assignment = EmployeeClientAssignment::where('employee_id', $this->id)
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->first();

        if ($assignment && $assignment->commission_type !== null) {
            $commissionType = $assignment->commission_type;
            $commissionValue = $assignment->commission_value;
            $baseAmount = $assignment->custom_package_amount ?? $client->current_package;
        } else {
            $commissionType = $this->commission_type;
            $commissionValue = $this->commission_value;
            $baseAmount = $client->current_package;
        }

        if ($commissionType === 'fixed_amount') {
            return $commissionValue;
        } elseif ($commissionType === 'percentage') {
            return ($baseAmount * $commissionValue) / 100;
        }
        return 0;
    }

    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
