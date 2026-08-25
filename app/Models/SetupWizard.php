<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupWizard extends Model
{
    protected $table = 'setup_wizard';
    protected $fillable = ['completed', 'completed_at'];
    protected $casts = ['completed' => 'boolean', 'completed_at' => 'datetime'];

    public static function isCompleted(): bool
    {
        $setup = static::first();
        return $setup && $setup->completed;
    }
}
