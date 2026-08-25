<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPackageHistory extends Model
{
    protected $table = 'client_package_history';

    protected $fillable = [
        'client_id', 'old_package', 'new_package', 'change_date', 'changed_by', 'reason',
    ];

    protected $casts = [
        'change_date' => 'date',
        'old_package' => 'decimal:2',
        'new_package' => 'decimal:2',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function changedByUser() { return $this->belongsTo(User::class, 'changed_by'); }
}
