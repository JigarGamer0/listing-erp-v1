-- ============================================================
-- Listing ERP — Complete Supabase (PostgreSQL) Schema
-- Converted from MySQL → PostgreSQL
-- Run this in Supabase SQL Editor (in order)
-- ============================================================

-- ============================================================
-- PART 1: CORE / AUTH TABLES
-- ============================================================

-- 1. users
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email_verified_at TIMESTAMPTZ DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    status TEXT NOT NULL DEFAULT 'active',
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT users_username_unique UNIQUE (username),
    CONSTRAINT users_email_unique UNIQUE (email),
    CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive'))
);
CREATE INDEX IF NOT EXISTS users_status_index ON users (status);
CREATE INDEX IF NOT EXISTS users_username_index ON users (username);

-- 2. password_reset_tokens
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL
);

-- 3. sessions
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL,

    CONSTRAINT sessions_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions (last_activity);

-- ============================================================
-- PART 2: CACHE TABLES
-- ============================================================

-- 4. cache
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- 5. cache_locks
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- ============================================================
-- PART 3: SETTINGS / CONFIG TABLES
-- ============================================================

-- 6. settings
CREATE TABLE IF NOT EXISTS settings (
    id BIGSERIAL PRIMARY KEY,
    "group" VARCHAR(255) NOT NULL DEFAULT 'general',
    key VARCHAR(191) NOT NULL,
    value TEXT DEFAULT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'text',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT settings_key_unique UNIQUE (key)
);
CREATE INDEX IF NOT EXISTS settings_group_index ON settings ("group");

-- 7. setup_wizard
CREATE TABLE IF NOT EXISTS setup_wizard (
    id BIGSERIAL PRIMARY KEY,
    completed BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL
);

-- ============================================================
-- PART 4: EXPENSE CATEGORIES
-- ============================================================

-- 8. expense_categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT expense_categories_status_check CHECK (status IN ('active', 'inactive'))
);

-- ============================================================
-- PART 5: EMPLOYEES (before clients, because clients.assigned_employee_id references employees)
-- ============================================================

-- 9. employees
CREATE TABLE IF NOT EXISTS employees (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    joining_date DATE NOT NULL,
    role_title VARCHAR(255) DEFAULT NULL,
    salary_type TEXT NOT NULL DEFAULT 'fixed',
    fixed_salary NUMERIC(12, 2) NOT NULL DEFAULT 0,
    commission_type TEXT NOT NULL DEFAULT 'fixed_amount',
    commission_value NUMERIC(12, 2) NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT employees_salary_type_check CHECK (salary_type IN ('fixed', 'package_based', 'both')),
    CONSTRAINT employees_commission_type_check CHECK (commission_type IN ('fixed_amount', 'percentage')),
    CONSTRAINT employees_status_check CHECK (status IN ('active', 'inactive', 'archived')),
    CONSTRAINT employees_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS employees_status_index ON employees (status);
CREATE INDEX IF NOT EXISTS employees_name_index ON employees (name);

-- ============================================================
-- PART 6: CLIENTS
-- ============================================================

-- 10. clients
CREATE TABLE IF NOT EXISTS clients (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    mobile_secondary VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    joining_date DATE NOT NULL,
    service_start_date DATE NOT NULL,
    current_package NUMERIC(12, 2) NOT NULL DEFAULT 0,
    current_flipkart_gst INTEGER NOT NULL DEFAULT 0,
    current_meesho_gst INTEGER NOT NULL DEFAULT 0,
    work_location TEXT NOT NULL DEFAULT 'our_office',
    manager_id BIGINT DEFAULT NULL,
    assigned_employee_id BIGINT DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_by BIGINT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT clients_work_location_check CHECK (work_location IN ('client_office', 'our_office', 'hybrid')),
    CONSTRAINT clients_status_check CHECK (status IN ('active', 'inactive', 'archived')),
    CONSTRAINT clients_manager_id_foreign FOREIGN KEY (manager_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT clients_assigned_employee_id_foreign FOREIGN KEY (assigned_employee_id) REFERENCES employees (id) ON DELETE SET NULL,
    CONSTRAINT clients_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS clients_status_index ON clients (status);
CREATE INDEX IF NOT EXISTS clients_mobile_index ON clients (mobile);
CREATE INDEX IF NOT EXISTS clients_name_index ON clients (name);
CREATE INDEX IF NOT EXISTS clients_service_start_date_index ON clients (service_start_date);

-- ============================================================
-- PART 7: CLIENT HISTORY TABLES
-- ============================================================

-- 11. client_package_history
CREATE TABLE IF NOT EXISTS client_package_history (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    old_package NUMERIC(12, 2) NOT NULL,
    new_package NUMERIC(12, 2) NOT NULL,
    change_date DATE NOT NULL,
    changed_by BIGINT NOT NULL,
    reason TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_package_history_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_package_history_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_package_history_client_id_index ON client_package_history (client_id);
CREATE INDEX IF NOT EXISTS client_package_history_change_date_index ON client_package_history (change_date);

-- 12. client_gst_history
CREATE TABLE IF NOT EXISTS client_gst_history (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    gst_type TEXT NOT NULL,
    old_amount INTEGER NOT NULL DEFAULT 0,
    new_amount INTEGER NOT NULL DEFAULT 0,
    change_date DATE NOT NULL,
    changed_by BIGINT NOT NULL,
    reason TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_gst_history_gst_type_check CHECK (gst_type IN ('flipkart', 'meesho')),
    CONSTRAINT client_gst_history_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_gst_history_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_gst_history_client_gst_index ON client_gst_history (client_id, gst_type);
CREATE INDEX IF NOT EXISTS client_gst_history_change_date_index ON client_gst_history (change_date);

-- 13. client_manager_history
CREATE TABLE IF NOT EXISTS client_manager_history (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    old_manager_id BIGINT DEFAULT NULL,
    new_manager_id BIGINT DEFAULT NULL,
    change_date DATE NOT NULL,
    changed_by BIGINT NOT NULL,
    reason TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_manager_history_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_manager_history_old_manager_foreign FOREIGN KEY (old_manager_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT client_manager_history_new_manager_foreign FOREIGN KEY (new_manager_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT client_manager_history_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_manager_history_client_id_index ON client_manager_history (client_id);

-- ============================================================
-- PART 8: BILLING & PAYMENTS
-- ============================================================

-- 14. client_billing_cycles
CREATE TABLE IF NOT EXISTS client_billing_cycles (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    billing_start DATE NOT NULL,
    billing_end DATE NOT NULL,
    package_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    flipkart_gst NUMERIC(12, 2) NOT NULL DEFAULT 0,
    meesho_gst NUMERIC(12, 2) NOT NULL DEFAULT 0,
    total_due NUMERIC(12, 2) NOT NULL DEFAULT 0,
    total_paid NUMERIC(12, 2) NOT NULL DEFAULT 0,
    balance NUMERIC(12, 2) NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_billing_cycles_status_check CHECK (status IN ('pending', 'partial', 'paid', 'advance', 'overdue')),
    CONSTRAINT client_billing_cycles_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_billing_cycles_client_start_index ON client_billing_cycles (client_id, billing_start);
CREATE INDEX IF NOT EXISTS client_billing_cycles_status_index ON client_billing_cycles (status);
CREATE INDEX IF NOT EXISTS client_billing_cycles_billing_end_index ON client_billing_cycles (billing_end);

-- 15. client_payments
CREATE TABLE IF NOT EXISTS client_payments (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    billing_cycle_id BIGINT DEFAULT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method TEXT NOT NULL DEFAULT 'cash',
    reference_number VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    received_by BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_payments_method_check CHECK (payment_method IN ('cash', 'bank_transfer', 'upi', 'cheque', 'other')),
    CONSTRAINT client_payments_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_payments_billing_cycle_id_foreign FOREIGN KEY (billing_cycle_id) REFERENCES client_billing_cycles (id) ON DELETE SET NULL,
    CONSTRAINT client_payments_received_by_foreign FOREIGN KEY (received_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_payments_client_date_index ON client_payments (client_id, payment_date);
CREATE INDEX IF NOT EXISTS client_payments_date_index ON client_payments (payment_date);

-- 16. client_payment_ledger
CREATE TABLE IF NOT EXISTS client_payment_ledger (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(255) NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_payment_ledger_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);

-- ============================================================
-- PART 9: CLIENT DETAILS
-- ============================================================

-- 17. client_accounts
CREATE TABLE IF NOT EXISTS client_accounts (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    platform VARCHAR(255) NOT NULL,
    store_name VARCHAR(255) NOT NULL,
    login_id VARCHAR(255) NOT NULL,
    login_password TEXT NOT NULL,
    notes TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_accounts_status_check CHECK (status IN ('active', 'inactive')),
    CONSTRAINT client_accounts_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_accounts_client_platform_index ON client_accounts (client_id, platform);

-- 18. client_documents
CREATE TABLE IF NOT EXISTS client_documents (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(255) DEFAULT NULL,
    file_size INTEGER NOT NULL DEFAULT 0,
    uploaded_by BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_documents_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_documents_client_id_index ON client_documents (client_id);

-- 19. client_notes
CREATE TABLE IF NOT EXISTS client_notes (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    note TEXT NOT NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_notes_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_notes_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS client_notes_client_id_index ON client_notes (client_id);

-- 20. client_timeline
CREATE TABLE IF NOT EXISTS client_timeline (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    metadata JSONB DEFAULT NULL,
    created_by BIGINT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT client_timeline_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT client_timeline_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS client_timeline_client_created_index ON client_timeline (client_id, created_at);
CREATE INDEX IF NOT EXISTS client_timeline_event_type_index ON client_timeline (event_type);

-- ============================================================
-- PART 10: EMPLOYEE TABLES
-- ============================================================

-- 21. employee_client_assignments
CREATE TABLE IF NOT EXISTS employee_client_assignments (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    client_id BIGINT NOT NULL,
    assigned_date DATE NOT NULL,
    unassigned_date DATE DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    commission_type TEXT DEFAULT NULL,
    commission_value NUMERIC(12, 2) NOT NULL DEFAULT 0,
    gst_count INTEGER NOT NULL DEFAULT 0,
    gst_platform VARCHAR(255) DEFAULT NULL,
    custom_package_amount NUMERIC(12, 2) DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT eca_status_check CHECK (status IN ('active', 'inactive')),
    CONSTRAINT eca_commission_type_check CHECK (commission_type IS NULL OR commission_type IN ('fixed_amount', 'percentage')),
    CONSTRAINT eca_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT eca_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS eca_employee_status_index ON employee_client_assignments (employee_id, status);
CREATE INDEX IF NOT EXISTS eca_client_status_index ON employee_client_assignments (client_id, status);

-- 22. employee_commissions
CREATE TABLE IF NOT EXISTS employee_commissions (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    client_id BIGINT NOT NULL,
    billing_cycle_id BIGINT DEFAULT NULL,
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    package_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    commission_type TEXT NOT NULL,
    commission_value NUMERIC(12, 2) NOT NULL DEFAULT 0,
    calculated_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending',
    paid_date DATE DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT ec_commission_type_check CHECK (commission_type IN ('fixed_amount', 'percentage')),
    CONSTRAINT ec_status_check CHECK (status IN ('pending', 'paid')),
    CONSTRAINT ec_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT ec_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT ec_billing_cycle_id_foreign FOREIGN KEY (billing_cycle_id) REFERENCES client_billing_cycles (id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS ec_employee_month_year_index ON employee_commissions (employee_id, month, year);
CREATE INDEX IF NOT EXISTS ec_employee_status_index ON employee_commissions (employee_id, status);

-- 23. employee_salaries
CREATE TABLE IF NOT EXISTS employee_salaries (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    base_salary NUMERIC(12, 2) NOT NULL DEFAULT 0,
    total_commission NUMERIC(12, 2) NOT NULL DEFAULT 0,
    advance_deduction NUMERIC(12, 2) NOT NULL DEFAULT 0,
    other_deductions NUMERIC(12, 2) NOT NULL DEFAULT 0,
    bonus NUMERIC(12, 2) NOT NULL DEFAULT 0,
    net_payable NUMERIC(12, 2) NOT NULL DEFAULT 0,
    paid_amount NUMERIC(12, 2) NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'pending',
    paid_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT es_status_check CHECK (status IN ('pending', 'partial', 'paid')),
    CONSTRAINT es_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT employee_salaries_unique UNIQUE (employee_id, month, year)
);
CREATE INDEX IF NOT EXISTS es_status_index ON employee_salaries (status);

-- 24. employee_advances
CREATE TABLE IF NOT EXISTS employee_advances (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    advance_date DATE NOT NULL,
    deducted NUMERIC(12, 2) NOT NULL DEFAULT 0,
    remaining NUMERIC(12, 2) NOT NULL DEFAULT 0,
    notes TEXT DEFAULT NULL,
    approved_by BIGINT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT ea_status_check CHECK (status IN ('active', 'partially_deducted', 'fully_deducted')),
    CONSTRAINT ea_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT ea_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS ea_employee_status_index ON employee_advances (employee_id, status);

-- 25. employee_advance_requests
CREATE TABLE IF NOT EXISTS employee_advance_requests (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    notes TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    rejection_reason TEXT DEFAULT NULL,
    action_by BIGINT DEFAULT NULL,
    action_at TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT ear_status_check CHECK (status IN ('pending', 'approved', 'rejected')),
    CONSTRAINT ear_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT ear_action_by_foreign FOREIGN KEY (action_by) REFERENCES users (id) ON DELETE SET NULL
);

-- 26. employee_holiday_requests
CREATE TABLE IF NOT EXISTS employee_holiday_requests (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    rejection_reason TEXT DEFAULT NULL,
    action_by BIGINT DEFAULT NULL,
    action_at TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT ehr_status_check CHECK (status IN ('pending', 'approved', 'rejected')),
    CONSTRAINT ehr_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT ehr_action_by_foreign FOREIGN KEY (action_by) REFERENCES users (id) ON DELETE SET NULL
);

-- 27. employee_daily_work_logs
CREATE TABLE IF NOT EXISTS employee_daily_work_logs (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    client_id BIGINT NOT NULL,
    log_date DATE NOT NULL,
    listings_count INTEGER NOT NULL DEFAULT 0,
    is_done BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT edwl_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE,
    CONSTRAINT edwl_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT emp_client_date_unique UNIQUE (employee_id, client_id, log_date)
);

-- ============================================================
-- PART 11: EXPENSES
-- ============================================================

-- 28. expenses
CREATE TABLE IF NOT EXISTS expenses (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category_id BIGINT NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    expense_date DATE NOT NULL,
    type TEXT NOT NULL DEFAULT 'one_time',
    notes TEXT DEFAULT NULL,
    receipt VARCHAR(255) DEFAULT NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT expenses_type_check CHECK (type IN ('monthly', 'one_time')),
    CONSTRAINT expenses_category_id_foreign FOREIGN KEY (category_id) REFERENCES expense_categories (id) ON DELETE CASCADE,
    CONSTRAINT expenses_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS expenses_date_index ON expenses (expense_date);
CREATE INDEX IF NOT EXISTS expenses_category_index ON expenses (category_id);
CREATE INDEX IF NOT EXISTS expenses_type_index ON expenses (type);

-- ============================================================
-- PART 12: INVESTMENTS & INVESTORS
-- ============================================================

-- 29. investors
CREATE TABLE IF NOT EXISTS investors (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_by BIGINT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT investors_status_check CHECK (status IN ('active', 'inactive')),
    CONSTRAINT investors_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
);

-- 30. investments
CREATE TABLE IF NOT EXISTS investments (
    id BIGSERIAL PRIMARY KEY,
    investor_id BIGINT DEFAULT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    investment_date DATE NOT NULL,
    notes TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'uncleared',
    expense_id BIGINT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT investments_status_check CHECK (status IN ('uncleared', 'cleared')),
    CONSTRAINT investments_investor_id_foreign FOREIGN KEY (investor_id) REFERENCES investors (id) ON DELETE SET NULL,
    CONSTRAINT investments_expense_id_foreign FOREIGN KEY (expense_id) REFERENCES expenses (id) ON DELETE SET NULL
);

-- ============================================================
-- PART 13: NOTIFICATIONS & FOLLOW-UPS
-- ============================================================

-- 31. notifications
CREATE TABLE IF NOT EXISTS notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT DEFAULT NULL,
    type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB DEFAULT NULL,
    read_at TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS notifications_user_read_index ON notifications (user_id, read_at);
CREATE INDEX IF NOT EXISTS notifications_type_index ON notifications (type);

-- 32. follow_ups
CREATE TABLE IF NOT EXISTS follow_ups (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    follow_up_date DATE NOT NULL,
    note TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    created_by BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,
    deleted_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT follow_ups_status_check CHECK (status IN ('pending', 'completed', 'cancelled')),
    CONSTRAINT follow_ups_client_id_foreign FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    CONSTRAINT follow_ups_created_by_foreign FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS follow_ups_date_status_index ON follow_ups (follow_up_date, status);
CREATE INDEX IF NOT EXISTS follow_ups_client_id_index ON follow_ups (client_id);

-- ============================================================
-- PART 14: SPATIE PERMISSIONS (RBAC)
-- ============================================================

-- 33. permissions
CREATE TABLE IF NOT EXISTS permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT permissions_name_guard_unique UNIQUE (name, guard_name)
);

-- 34. roles
CREATE TABLE IF NOT EXISTS roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT roles_name_guard_unique UNIQUE (name, guard_name)
);

-- 35. model_has_permissions
CREATE TABLE IF NOT EXISTS model_has_permissions (
    permission_id BIGINT NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,

    CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type),
    CONSTRAINT mhp_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS mhp_model_id_model_type_index ON model_has_permissions (model_id, model_type);

-- 36. model_has_roles
CREATE TABLE IF NOT EXISTS model_has_roles (
    role_id BIGINT NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,

    CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type),
    CONSTRAINT mhr_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS mhr_model_id_model_type_index ON model_has_roles (model_id, model_type);

-- 37. role_has_permissions
CREATE TABLE IF NOT EXISTS role_has_permissions (
    permission_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,

    CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id),
    CONSTRAINT rhp_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE,
    CONSTRAINT rhp_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

-- ============================================================
-- PART 15: ACTIVITY LOG (Spatie)
-- ============================================================

-- 38. activity_log
CREATE TABLE IF NOT EXISTS activity_log (
    id BIGSERIAL PRIMARY KEY,
    log_name VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    subject_type VARCHAR(255) DEFAULT NULL,
    subject_id BIGINT DEFAULT NULL,
    event VARCHAR(255) DEFAULT NULL,
    causer_type VARCHAR(255) DEFAULT NULL,
    causer_id BIGINT DEFAULT NULL,
    properties JSONB DEFAULT NULL,
    batch_uuid UUID DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL
);
CREATE INDEX IF NOT EXISTS activity_log_log_name_index ON activity_log (log_name);
CREATE INDEX IF NOT EXISTS activity_log_subject_index ON activity_log (subject_type, subject_id);
CREATE INDEX IF NOT EXISTS activity_log_causer_index ON activity_log (causer_type, causer_id);

-- ============================================================
-- PART 16: REPORTS & ALIAS TABLES
-- ============================================================

-- 39. reports
CREATE TABLE IF NOT EXISTS reports (
    id BIGSERIAL PRIMARY KEY,
    report_type VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    generated_by BIGINT DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL,

    CONSTRAINT reports_generated_by_foreign FOREIGN KEY (generated_by) REFERENCES users (id) ON DELETE SET NULL
);

-- 40. activity_logs (alias table)
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGSERIAL PRIMARY KEY,
    log_name VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    subject_type VARCHAR(255) DEFAULT NULL,
    subject_id BIGINT DEFAULT NULL,
    causer_type VARCHAR(255) DEFAULT NULL,
    causer_id BIGINT DEFAULT NULL,
    properties JSONB DEFAULT NULL,
    batch_uuid UUID DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NULL,
    updated_at TIMESTAMPTZ DEFAULT NULL
);

-- ============================================================
-- PART 17: Laravel Migrations Table (Required for Laravel)
-- ============================================================

CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- ============================================================
-- DONE! All 40 tables created successfully.
-- ============================================================
