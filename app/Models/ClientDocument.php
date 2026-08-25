<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'title', 'file_path', 'file_name', 'file_type', 'file_size', 'uploaded_by',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function uploadedByUser() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
