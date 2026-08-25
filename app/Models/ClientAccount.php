<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ClientAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'platform', 'store_name', 'login_id',
        'login_password', 'notes', 'status',
    ];

    // Encrypt password on set
    public function setLoginPasswordAttribute($value)
    {
        $this->attributes['login_password'] = Crypt::encryptString($value);
    }

    // Decrypt password on get
    public function getLoginPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function client() { return $this->belongsTo(Client::class); }
}
