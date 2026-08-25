<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'mobile', 'email', 'address', 'notes', 'status', 'created_by',
    ];

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Scope for active investors
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Total invested amount
    public function getTotalInvestedAttribute()
    {
        return $this->investments()->sum('amount');
    }

    // Total uncleared amount
    public function getTotalUnclearedAttribute()
    {
        return $this->investments()->where('status', 'uncleared')->sum('amount');
    }

    // Total cleared amount
    public function getTotalClearedAttribute()
    {
        return $this->investments()->where('status', 'cleared')->sum('amount');
    }
}
