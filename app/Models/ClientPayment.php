<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClientPayment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'client_id', 'billing_cycle_id', 'amount', 'payment_date',
        'payment_method', 'reference_number', 'notes', 'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['client_id', 'amount', 'payment_date', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function billingCycle() { return $this->belongsTo(ClientBillingCycle::class, 'billing_cycle_id'); }
    public function receivedByUser() { return $this->belongsTo(User::class, 'received_by'); }
}
