<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientNote extends Model
{
    use SoftDeletes;
    protected $fillable = ['client_id', 'note', 'created_by'];

    public function client() { return $this->belongsTo(Client::class); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by'); }
}
