<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBillingCycle extends Model
{
    protected $fillable = [
        'client_id', 'billing_start', 'billing_end', 'package_amount',
        'flipkart_gst', 'meesho_gst', 'total_due', 'total_paid', 'balance', 'status',
    ];

    protected $casts = [
        'billing_start' => 'date',
        'billing_end' => 'date',
        'package_amount' => 'decimal:2',
        'flipkart_gst' => 'decimal:2',
        'meesho_gst' => 'decimal:2',
        'total_due' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function client() { return $this->belongsTo(Client::class); }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class, 'billing_cycle_id');
    }

    public function recalculate()
    {
        $this->total_paid = $this->payments()->sum('amount');
        $this->balance = $this->total_due - $this->total_paid;

        if ($this->total_paid <= 0) {
            $this->status = $this->billing_end->isPast() ? 'overdue' : 'pending';
        } elseif ($this->total_paid >= $this->total_due) {
            $this->status = $this->total_paid > $this->total_due ? 'advance' : 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }

    // Scopes
    public function scopePending($query) { return $query->whereIn('status', ['pending', 'partial', 'overdue']); }
    public function scopeOverdue($query) { return $query->where('status', 'overdue'); }
    public function scopePaid($query) { return $query->where('status', 'paid'); }
}
