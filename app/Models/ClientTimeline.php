<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTimeline extends Model
{
    protected $table = 'client_timeline';

    protected $fillable = [
        'client_id', 'event_type', 'description', 'metadata', 'created_by',
    ];

    protected $casts = ['metadata' => 'array'];

    public function client() { return $this->belongsTo(Client::class); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by'); }
}
