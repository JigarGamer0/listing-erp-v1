<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientManagerHistory extends Model
{
    protected $table = 'client_manager_history';

    protected $fillable = [
        'client_id', 'old_manager_id', 'new_manager_id', 'change_date', 'changed_by', 'reason',
    ];

    protected $casts = ['change_date' => 'date'];

    public function client() { return $this->belongsTo(Client::class); }
    public function oldManager() { return $this->belongsTo(User::class, 'old_manager_id'); }
    public function newManager() { return $this->belongsTo(User::class, 'new_manager_id'); }
    public function changedByUser() { return $this->belongsTo(User::class, 'changed_by'); }
}
