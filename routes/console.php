<?php

use Illuminate\Support\Facades\Schedule;

// Mark overdue billing cycles daily
Schedule::call(function () {
    (new \App\Services\BillingService())->markOverdueCycles();
})->daily();

// Generate notifications daily
Schedule::command('notifications:generate')->daily();
