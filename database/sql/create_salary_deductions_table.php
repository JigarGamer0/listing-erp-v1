<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('
    CREATE TABLE IF NOT EXISTS employee_salary_deductions (
        id BIGSERIAL PRIMARY KEY,
        employee_id BIGINT NOT NULL,
        month INTEGER NOT NULL,
        year INTEGER NOT NULL,
        amount NUMERIC(12, 2) NOT NULL,
        reason TEXT NOT NULL,
        created_by BIGINT DEFAULT NULL,
        created_at TIMESTAMPTZ DEFAULT NULL,
        updated_at TIMESTAMPTZ DEFAULT NULL,
        CONSTRAINT esd_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
        CONSTRAINT esd_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
    );
');

DB::statement('CREATE INDEX IF NOT EXISTS esd_employee_month_year_idx ON employee_salary_deductions (employee_id, month, year);');

echo "Table employee_salary_deductions created successfully.\n";
