<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientGstHistory extends Model
{
    protected $table = 'client_gst_history';

    protected $fillable = [
        'client_id', 'gst_type', 'old_amount', 'new_amount', 'change_date', 'changed_by', 'reason',
    ];

    protected $casts = [
        'change_date' => 'date',
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function changedByUser() { return $this->belongsTo(User::class, 'changed_by'); }
}
