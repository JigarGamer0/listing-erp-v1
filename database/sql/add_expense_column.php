<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('ALTER TABLE expenses ADD COLUMN IF NOT EXISTS include_in_calculation BOOLEAN DEFAULT TRUE;');
DB::statement('UPDATE expenses SET include_in_calculation = TRUE WHERE include_in_calculation IS NULL;');

echo "Column include_in_calculation added successfully.\n";
