<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'follow_up_date', 'note', 'status', 'created_by',
    ];

    protected $casts = ['follow_up_date' => 'date'];

    public function client() { return $this->belongsTo(Client::class); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeToday($query) { return $query->whereDate('follow_up_date', today()); }
    public function scopeUpcoming($query) { return $query->where('follow_up_date', '>=', today())->where('status', 'pending'); }
}
