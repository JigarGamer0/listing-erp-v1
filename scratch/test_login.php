<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing user login...\n";

$credentials = [
    'username' => 'admin',
    'password' => 'Jigar@CRM2026#Secure',
];

if (Auth::attempt($credentials)) {
    echo "SUCCESS: Login successful! User authenticated: " . Auth::user()->name . "\n";
    
    // Test if dashboard data can load
    $dashboardController = new \App\Http\Controllers\DashboardController();
    $request = \Illuminate\Http\Request::create('/dashboard', 'GET');
    $response = $dashboardController->index($request);
    
    if ($response instanceof \Illuminate\View\View) {
        echo "SUCCESS: Dashboard view loaded successfully!\n";
    } else {
        echo "ERROR: Dashboard returned unexpected response type.\n";
    }
} else {
    echo "ERROR: Login failed with credentials.\n";
}
