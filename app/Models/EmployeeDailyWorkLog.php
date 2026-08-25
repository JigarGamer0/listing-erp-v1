<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDailyWorkLog extends Model
{
    protected $fillable = [
        'employee_id', 'client_id', 'log_date', 'listings_count', 'is_done', 'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'is_done' => 'boolean',
        'listings_count' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
