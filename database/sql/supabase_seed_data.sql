-- ============================================================
-- Listing ERP — Supabase Seed Data
-- Run this AFTER supabase_schema.sql
-- ============================================================

-- ============================================================
-- 1. PERMISSIONS (Spatie - guard: web)
-- ============================================================

INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
-- Client permissions
('clients.view', 'web', NOW(), NOW()),
('clients.create', 'web', NOW(), NOW()),
('clients.edit', 'web', NOW(), NOW()),
('clients.delete', 'web', NOW(), NOW()),
('clients.change_package', 'web', NOW(), NOW()),
('clients.change_gst', 'web', NOW(), NOW()),
('clients.change_manager', 'web', NOW(), NOW()),
('clients.view_accounts', 'web', NOW(), NOW()),
('clients.manage_accounts', 'web', NOW(), NOW()),
('clients.view_documents', 'web', NOW(), NOW()),
('clients.manage_documents', 'web', NOW(), NOW()),
('clients.view_notes', 'web', NOW(), NOW()),
('clients.manage_notes', 'web', NOW(), NOW()),
('clients.view_timeline', 'web', NOW(), NOW()),
-- Payment permissions
('payments.view', 'web', NOW(), NOW()),
('payments.create', 'web', NOW(), NOW()),
('payments.edit', 'web', NOW(), NOW()),
('payments.delete', 'web', NOW(), NOW()),
-- Employee permissions
('employees.view', 'web', NOW(), NOW()),
('employees.create', 'web', NOW(), NOW()),
('employees.edit', 'web', NOW(), NOW()),
('employees.delete', 'web', NOW(), NOW()),
('employees.assign_clients', 'web', NOW(), NOW()),
-- Salary permissions
('salary.view', 'web', NOW(), NOW()),
('salary.generate', 'web', NOW(), NOW()),
('salary.pay', 'web', NOW(), NOW()),
('salary.advance', 'web', NOW(), NOW()),
-- Expense permissions
('expenses.view', 'web', NOW(), NOW()),
('expenses.create', 'web', NOW(), NOW()),
('expenses.edit', 'web', NOW(), NOW()),
('expenses.delete', 'web', NOW(), NOW()),
-- Report permissions
('reports.view', 'web', NOW(), NOW()),
('reports.export', 'web', NOW(), NOW()),
-- Activity log permissions
('activity_logs.view', 'web', NOW(), NOW()),
-- Settings permissions
('settings.view', 'web', NOW(), NOW()),
('settings.edit', 'web', NOW(), NOW()),
('users.view', 'web', NOW(), NOW()),
('users.create', 'web', NOW(), NOW()),
('users.edit', 'web', NOW(), NOW()),
('users.delete', 'web', NOW(), NOW()),
-- Notification permissions
('notifications.view', 'web', NOW(), NOW()),
-- Follow-up permissions
('follow_ups.view', 'web', NOW(), NOW()),
('follow_ups.create', 'web', NOW(), NOW()),
('follow_ups.edit', 'web', NOW(), NOW())
ON CONFLICT (name, guard_name) DO NOTHING;

-- ============================================================
-- 2. ROLES
-- ============================================================

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('Main Admin', 'web', NOW(), NOW()),
('Admin', 'web', NOW(), NOW()),
('Employee', 'web', NOW(), NOW())
ON CONFLICT (name, guard_name) DO NOTHING;

-- ============================================================
-- 3. ROLE_HAS_PERMISSIONS — Main Admin gets ALL permissions
-- ============================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Main Admin'
ON CONFLICT DO NOTHING;

-- ============================================================
-- 4. ROLE_HAS_PERMISSIONS — Admin gets selected permissions
-- ============================================================

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Admin'
AND p.name IN (
    'clients.view', 'clients.create', 'clients.edit',
    'clients.change_package', 'clients.change_gst', 'clients.change_manager',
    'clients.view_accounts', 'clients.manage_accounts',
    'clients.view_documents', 'clients.manage_documents',
    'clients.view_notes', 'clients.manage_notes',
    'clients.view_timeline',
    'payments.view', 'payments.create', 'payments.edit',
    'employees.view', 'employees.create', 'employees.edit',
    'employees.assign_clients',
    'salary.view', 'salary.generate', 'salary.pay', 'salary.advance',
    'expenses.view', 'expenses.create', 'expenses.edit',
    'reports.view', 'reports.export',
    'notifications.view',
    'follow_ups.view', 'follow_ups.create', 'follow_ups.edit'
)
ON CONFLICT DO NOTHING;

-- ============================================================
-- 5. DEFAULT ADMIN USER
-- Password: Admin@123 (bcrypt hash)
-- ============================================================

INSERT INTO users (name, username, email, password, must_change_password, status, email_verified_at, created_at, updated_at) VALUES
(
    'Main Admin',
    'admin',
    'admin@listingerp.com',
    '$2y$12$hNB1EL1FDLN5pduGmj/7xOKVlFeDKXdnSzT1RH5dUFGvhLe0dSOSu',
    TRUE,
    'active',
    NOW(),
    NOW(),
    NOW()
)
ON CONFLICT (username) DO NOTHING;

-- Assign Main Admin role to admin user
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\Models\User', u.id
FROM roles r, users u
WHERE r.name = 'Main Admin' AND u.username = 'admin'
ON CONFLICT DO NOTHING;

-- ============================================================
-- 6. SETTINGS
-- ============================================================

INSERT INTO settings ("group", key, value, type, created_at, updated_at) VALUES
('general', 'company_name', 'Listing ERP', 'text', NOW(), NOW()),
('general', 'company_logo', NULL, 'file', NOW(), NOW()),
('general', 'currency', '₹', 'text', NOW(), NOW()),
('general', 'currency_code', 'INR', 'text', NOW(), NOW()),
('general', 'timezone', 'Asia/Kolkata', 'text', NOW(), NOW()),
('general', 'date_format', 'd/m/Y', 'text', NOW(), NOW()),
('general', 'default_theme', 'light', 'select', NOW(), NOW())
ON CONFLICT (key) DO NOTHING;

-- ============================================================
-- 7. EXPENSE CATEGORIES
-- ============================================================

INSERT INTO expense_categories (name, description, status, created_at, updated_at) VALUES
('Rent', 'Office rent and related expenses', 'active', NOW(), NOW()),
('Internet', 'Internet and broadband charges', 'active', NOW(), NOW()),
('Electricity', 'Electricity bills', 'active', NOW(), NOW()),
('Salary', 'Employee salary payments', 'active', NOW(), NOW()),
('Software', 'Software licenses and subscriptions', 'active', NOW(), NOW()),
('Marketing', 'Marketing and advertising expenses', 'active', NOW(), NOW()),
('Travel', 'Travel and conveyance', 'active', NOW(), NOW()),
('Office Supplies', 'Stationery and office supplies', 'active', NOW(), NOW()),
('Maintenance', 'Equipment and office maintenance', 'active', NOW(), NOW()),
('Other', 'Miscellaneous expenses', 'active', NOW(), NOW());

-- ============================================================
-- 8. SETUP WIZARD (mark as not completed for fresh setup)
-- ============================================================

INSERT INTO setup_wizard (completed, created_at, updated_at) VALUES
(FALSE, NOW(), NOW());

-- ============================================================
-- 9. LARAVEL MIGRATIONS TABLE (mark all migrations as run)
-- ============================================================

INSERT INTO migrations (migration, batch) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_settings_table', 1),
('0001_01_01_000003_create_expense_categories_table', 1),
('0001_01_01_000004_create_clients_table', 1),
('0001_01_01_000005_create_client_history_tables', 1),
('0001_01_01_000006_create_client_payments_table', 1),
('0001_01_01_000007_create_client_details_tables', 1),
('0001_01_01_000008_create_employee_tables', 1),
('0001_01_01_000009_create_expenses_table', 1),
('0001_01_01_000010_create_notifications_table', 1),
('2026_07_10_130925_create_permission_tables', 1),
('2026_07_10_130926_create_activity_log_table', 1),
('2026_07_10_130927_add_event_column_to_activity_log_table', 1),
('2026_07_10_130928_add_batch_uuid_column_to_activity_log_table', 1),
('2026_07_10_131000_create_required_alias_tables', 1),
('2026_07_10_141500_update_assigned_employee_and_gst_counts', 1),
('2026_07_10_150000_add_custom_commission_to_assignments', 1),
('2026_07_13_155720_add_gst_count_and_custom_package_to_assignments', 1),
('2026_07_13_161157_change_employee_advances_pending_status_to_active', 1),
('2026_07_13_162622_add_gst_platform_to_employee_client_assignments', 1),
('2026_07_13_164008_create_investments_table', 1),
('2026_07_13_164036_add_mobile_secondary_to_clients_table', 1),
('2026_07_13_200000_create_investors_table', 1),
('2026_07_13_211000_create_employee_advance_requests_table', 1),
('2026_07_13_213000_create_employee_holiday_requests_table', 1),
('2026_07_13_214000_add_rejection_reason_to_advance_requests_table', 1),
('2026_07_13_214500_create_employee_daily_work_logs_table', 1);

-- ============================================================
-- DONE! Seed data inserted successfully.
-- ============================================================
