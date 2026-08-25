<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'mobile', 'mobile_secondary', 'email', 'joining_date', 'service_start_date',
        'current_package', 'current_flipkart_gst', 'current_meesho_gst',
        'work_location', 'manager_id', 'assigned_employee_id', 'address',
        'status', 'created_by',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'service_start_date' => 'date',
        'current_package' => 'decimal:2',
        'current_flipkart_gst' => 'integer',
        'current_meesho_gst' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'mobile', 'current_package', 'current_flipkart_gst',
                'current_meesho_gst', 'work_location', 'manager_id',
                'assigned_employee_id', 'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function packageHistory()
    {
        return $this->hasMany(ClientPackageHistory::class)->orderByDesc('change_date');
    }

    public function gstHistory()
    {
        return $this->hasMany(ClientGstHistory::class)->orderByDesc('change_date');
    }

    public function managerHistory()
    {
        return $this->hasMany(ClientManagerHistory::class)->orderByDesc('change_date');
    }

    public function billingCycles()
    {
        return $this->hasMany(ClientBillingCycle::class)->orderByDesc('billing_start');
    }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class)->orderByDesc('payment_date');
    }

    public function accounts()
    {
        return $this->hasMany(ClientAccount::class);
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function notes()
    {
        return $this->hasMany(ClientNote::class)->orderByDesc('created_at');
    }

    public function timeline()
    {
        return $this->hasMany(ClientTimeline::class)->orderByDesc('created_at');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeClientAssignment::class);
    }

    // Computed attributes
    public function getTotalDueAttribute()
    {
        return $this->billingCycles()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getAdvanceBalanceAttribute()
    {
        $advanceCycles = $this->billingCycles()->where('status', 'advance')->sum('balance');
        return abs($advanceCycles);
    }

    public function getPendingMonthsAttribute()
    {
        return $this->billingCycles()
            ->whereIn('status', ['pending', 'overdue'])
            ->count();
    }

    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeInactive($query) { return $query->where('status', 'inactive'); }
    public function scopeArchived($query) { return $query->where('status', 'archived'); }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopePaymentDue($query)
    {
        return $query->whereHas('billingCycles', function ($q) {
            $q->whereIn('status', ['pending', 'partial', 'overdue']);
        });
    }
}
