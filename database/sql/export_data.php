<?php
/**
 * MySQL Data Export Script for Supabase Migration
 * Upload this file to your Hostinger public_html folder
 * Then access it via: https://your-domain.com/export_data.php
 */

// Prevent timeout
set_time_limit(300);
ini_set('memory_limit', '512M');

// MySQL connection (using .env values)
$mysqlHost = 'localhost';
$mysqlDB   = 'u826754371_listing_crm';
$mysqlUser = 'u826754371_listingadmin';
$mysqlPass = 'Jigar@CRM2026#Secure';

header('Content-Type: text/plain; charset=utf-8');

try {
    $mysql = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDB;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("MySQL Connection Error: " . $e->getMessage());
}

// Tables to export (in dependency order - skip seed tables)
$tables = [
    'users',
    'employees',
    'clients',
    'client_billing_cycles',
    'client_payments',
    'client_payment_ledger',
    'client_accounts',
    'client_documents',
    'client_notes',
    'client_timeline',
    'client_package_history',
    'client_gst_history',
    'client_manager_history',
    'employee_client_assignments',
    'employee_commissions',
    'employee_salaries',
    'employee_advances',
    'expenses',
    'investors',
    'investments',
    'notifications',
    'follow_ups',
    'employee_advance_requests',
    'employee_holiday_requests',
    'employee_daily_work_logs',
    'activity_log',
    'model_has_roles',
    'model_has_permissions',
];

echo "-- ============================================================\n";
echo "-- Listing ERP — MySQL Data Export for Supabase Import\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- ============================================================\n\n";

$totalRows = 0;

foreach ($tables as $table) {
    // Check if table exists
    try {
        $check = $mysql->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() === 0) {
            echo "-- SKIP: Table '$table' does not exist\n\n";
            continue;
        }
    } catch (Exception $e) {
        echo "-- SKIP: Table '$table' - " . $e->getMessage() . "\n\n";
        continue;
    }

    // Get all rows
    try {
        $stmt = $mysql->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "-- ERROR: Table '$table' - " . $e->getMessage() . "\n\n";
        continue;
    }

    if (empty($rows)) {
        echo "-- Table '$table': 0 rows (empty)\n\n";
        continue;
    }

    $columns = array_keys($rows[0]);
    
    // Quote column names (handle reserved words like "group", "key")
    $quotedCols = array_map(function($col) {
        $reserved = ['group','key','type','order','user','check','default','column','table','index','constraint','primary','references','select','insert','update','delete','from','where','and','or','not','null','true','false','like','in','between','as','on','join','left','right','inner','outer','create','drop','alter','add','set','values','into','is','by','desc','asc','limit','offset','having','case','when','then','else','end','exists','all','any','some','union','except','intersect'];
        if (in_array(strtolower($col), $reserved)) {
            return '"' . $col . '"';
        }
        return $col;
    }, $columns);
    
    $colList = implode(', ', $quotedCols);

    echo "-- ============================================================\n";
    echo "-- Table: $table (" . count($rows) . " rows)\n";
    echo "-- ============================================================\n";

    // For tables with auto-increment IDs, we need to handle sequences
    $hasId = in_array('id', $columns);

    foreach ($rows as $row) {
        $values = [];
        foreach ($row as $key => $val) {
            if ($val === null) {
                $values[] = 'NULL';
            } elseif (is_numeric($val) && !in_array($key, ['phone', 'mobile', 'mobile_secondary', 'reference_number', 'login_id', 'remember_token'])) {
                $values[] = $val;
            } else {
                // Escape single quotes for PostgreSQL
                $escaped = str_replace("'", "''", $val);
                // Handle boolean-like values
                if ($key === 'must_change_password' || $key === 'completed' || $key === 'is_done') {
                    $values[] = ($val == 1 || $val === 't' || $val === 'true') ? 'TRUE' : 'FALSE';
                } else {
                    $values[] = "'" . $escaped . "'";
                }
            }
        }
        $valList = implode(', ', $values);
        echo "INSERT INTO $table ($colList) VALUES ($valList) ON CONFLICT DO NOTHING;\n";
    }

    echo "\n";
    $totalRows += count($rows);
}

// Sequence reset statements
echo "-- ============================================================\n";
echo "-- SEQUENCE RESETS (run after data import)\n";
echo "-- ============================================================\n";

$seqTables = ['users','employees','clients','client_billing_cycles','client_payments',
    'client_payment_ledger','client_accounts','client_documents','client_notes',
    'client_timeline','client_package_history','client_gst_history',
    'client_manager_history','employee_client_assignments','employee_commissions',
    'employee_salaries','employee_advances','expenses','investors','investments',
    'notifications','follow_ups','employee_advance_requests','employee_holiday_requests',
    'employee_daily_work_logs','activity_log','expense_categories','permissions',
    'roles','reports','settings','setup_wizard'];

foreach ($seqTables as $t) {
    echo "SELECT setval('{$t}_id_seq', COALESCE((SELECT MAX(id) FROM $t), 0) + 1, false);\n";
}

echo "\n-- ============================================================\n";
echo "-- TOTAL ROWS EXPORTED: $totalRows\n";
echo "-- ============================================================\n";
