# MySQL → Supabase Data Migration Guide

## 📋 Step-by-Step Process

### Step 1: Supabase Project Setup

1. **Supabase Account** banao: https://supabase.com
2. **New Project** create karo
3. Project create hone ke baad, **Settings → Database** mein jaao
4. Yeh details note karo:
   - **Host** (e.g., `db.xxxxxxxxxxxx.supabase.co`)
   - **Database name** (usually `postgres`)
   - **Port** (usually `5432` ya `6543`)
   - **Password** (jo tumne project create karte waqt diya tha)

---

### Step 2: Schema Create Karo (Supabase SQL Editor)

1. Supabase Dashboard → **SQL Editor** jaao
2. **New Query** click karo
3. `supabase_schema.sql` file ka pura content paste karo
4. **Run** click karo ✅
5. Verify karo ki saare tables ban gaye: **Table Editor** mein check karo

---

### Step 3: Seed Data Insert Karo

1. SQL Editor mein naya query banao
2. `supabase_seed_data.sql` file ka pura content paste karo
3. **Run** click karo ✅
4. Verify karo:
   - `permissions` table mein 44 rows honi chahiye
   - `roles` table mein 3 roles hone chahiye (Main Admin, Admin, Employee)
   - `settings` table mein 7 rows honi chahiye
   - `expense_categories` mein 10 rows

---

### Step 4: MySQL Se Existing Data Export Karo

#### Option A: phpMyAdmin Se (Easy Way)

1. **phpMyAdmin** open karo (Hostinger panel se)
2. Database `u826754371_listing_crm` select karo
3. Har table ke liye:
   - Table click karo → **Export** tab
   - Format: **CSV** select karo
   - **Go** click karo
   - CSV file download ho jayegi

**⚠️ Important Tables jo export karne hain (data wali):**

| Priority | Table Name | Description |
|---|---|---|
| 🔴 HIGH | `users` | Saare users/admins |
| 🔴 HIGH | `employees` | Employee records |
| 🔴 HIGH | `clients` | Client records |
| 🔴 HIGH | `client_billing_cycles` | Billing data |
| 🔴 HIGH | `client_payments` | Payment records |
| 🟡 MED | `client_accounts` | Platform login details |
| 🟡 MED | `client_documents` | Document records |
| 🟡 MED | `client_notes` | Client notes |
| 🟡 MED | `client_timeline` | Timeline events |
| 🟡 MED | `client_package_history` | Package changes |
| 🟡 MED | `client_gst_history` | GST changes |
| 🟡 MED | `employee_client_assignments` | Assignments |
| 🟡 MED | `employee_commissions` | Commission data |
| 🟡 MED | `employee_salaries` | Salary records |
| 🟡 MED | `employee_advances` | Advance records |
| 🟡 MED | `expenses` | Expense records |
| 🟡 MED | `investors` | Investor records |
| 🟡 MED | `investments` | Investment records |
| 🟢 LOW | `notifications` | Notifications |
| 🟢 LOW | `follow_ups` | Follow-up reminders |
| 🟢 LOW | `employee_advance_requests` | Advance requests |
| 🟢 LOW | `employee_holiday_requests` | Holiday requests |
| 🟢 LOW | `employee_daily_work_logs` | Work logs |

**⚠️ Skip these tables** (seed data se already aa jayega):
- `permissions` ✅ (seed mein hai)
- `roles` ✅ (seed mein hai)
- `role_has_permissions` ✅ (seed mein hai)
- `settings` ✅ (seed mein hai)
- `expense_categories` ✅ (seed mein hai)
- `cache` / `cache_locks` (temporary data)
- `sessions` (temporary data)
- `migrations` ✅ (seed mein hai)

#### Option B: Command Line Se (Advanced)

```bash
# SSH se server pe jaao, fir:

# Users export
mysqldump -u u826754371_listingadmin -p u826754371_listing_crm users \
  --no-create-info --compatible=postgresql --complete-insert > users_data.sql

# Ya CSV mein:
mysql -u u826754371_listingadmin -p u826754371_listing_crm \
  -e "SELECT * FROM users" --batch > users.csv
```

---

### Step 5: Supabase Mein Data Import Karo

#### Option A: Supabase Dashboard Se (Recommended for small data)

1. **Table Editor** jaao
2. Table select karo (e.g., `users`)
3. **Insert Row** ya **Import data from CSV** button click karo
4. CSV file upload karo
5. Column mapping verify karo
6. **Import** karo

**⚠️ Import Order (Foreign Key dependency):**

```
1. users           (no dependency)
2. employees       (depends on users)
3. clients         (depends on users, employees)
4. client_billing_cycles  (depends on clients)
5. client_payments        (depends on clients, billing_cycles, users)
6. client_accounts        (depends on clients)
7. client_documents       (depends on clients, users)
8. client_notes           (depends on clients, users)
9. client_timeline        (depends on clients, users)
10. client_package_history (depends on clients, users)
11. client_gst_history     (depends on clients, users)
12. client_manager_history (depends on clients, users)
13. employee_client_assignments (depends on employees, clients)
14. employee_commissions   (depends on employees, clients, billing_cycles)
15. employee_salaries      (depends on employees)
16. employee_advances      (depends on employees, users)
17. expenses               (depends on expense_categories, users)
18. investors              (depends on users)
19. investments            (depends on investors, expenses)
20. notifications          (depends on users)
21. follow_ups             (depends on clients, users)
22. employee_advance_requests  (depends on employees, users)
23. employee_holiday_requests  (depends on employees, users)
24. employee_daily_work_logs   (depends on employees, clients)
25. model_has_roles        (depends on roles — ONLY if custom assignments exist)
26. model_has_permissions   (depends on permissions)
27. client_payment_ledger   (depends on clients)
```

#### Option B: SQL INSERT Statements Se

Agar phpMyAdmin se SQL format mein export kiya hai, toh:
1. SQL file open karo
2. MySQL-specific syntax replace karo:
   - `\'` → `''` (escape quotes)
   - `\n` → actual newlines
   - Backticks `` ` `` hatao
3. Supabase SQL Editor mein paste karke run karo

---

### Step 6: Sequences Reset Karo (IMPORTANT!)

Data import ke baad, PostgreSQL sequences reset karna zaroori hai, nahi toh naye records insert karte waqt ID conflict aayega:

```sql
-- Run this in Supabase SQL Editor AFTER importing all data

SELECT setval('users_id_seq', COALESCE((SELECT MAX(id) FROM users), 0) + 1, false);
SELECT setval('employees_id_seq', COALESCE((SELECT MAX(id) FROM employees), 0) + 1, false);
SELECT setval('clients_id_seq', COALESCE((SELECT MAX(id) FROM clients), 0) + 1, false);
SELECT setval('client_billing_cycles_id_seq', COALESCE((SELECT MAX(id) FROM client_billing_cycles), 0) + 1, false);
SELECT setval('client_payments_id_seq', COALESCE((SELECT MAX(id) FROM client_payments), 0) + 1, false);
SELECT setval('client_accounts_id_seq', COALESCE((SELECT MAX(id) FROM client_accounts), 0) + 1, false);
SELECT setval('client_documents_id_seq', COALESCE((SELECT MAX(id) FROM client_documents), 0) + 1, false);
SELECT setval('client_notes_id_seq', COALESCE((SELECT MAX(id) FROM client_notes), 0) + 1, false);
SELECT setval('client_timeline_id_seq', COALESCE((SELECT MAX(id) FROM client_timeline), 0) + 1, false);
SELECT setval('client_package_history_id_seq', COALESCE((SELECT MAX(id) FROM client_package_history), 0) + 1, false);
SELECT setval('client_gst_history_id_seq', COALESCE((SELECT MAX(id) FROM client_gst_history), 0) + 1, false);
SELECT setval('client_manager_history_id_seq', COALESCE((SELECT MAX(id) FROM client_manager_history), 0) + 1, false);
SELECT setval('client_payment_ledger_id_seq', COALESCE((SELECT MAX(id) FROM client_payment_ledger), 0) + 1, false);
SELECT setval('employee_client_assignments_id_seq', COALESCE((SELECT MAX(id) FROM employee_client_assignments), 0) + 1, false);
SELECT setval('employee_commissions_id_seq', COALESCE((SELECT MAX(id) FROM employee_commissions), 0) + 1, false);
SELECT setval('employee_salaries_id_seq', COALESCE((SELECT MAX(id) FROM employee_salaries), 0) + 1, false);
SELECT setval('employee_advances_id_seq', COALESCE((SELECT MAX(id) FROM employee_advances), 0) + 1, false);
SELECT setval('employee_advance_requests_id_seq', COALESCE((SELECT MAX(id) FROM employee_advance_requests), 0) + 1, false);
SELECT setval('employee_holiday_requests_id_seq', COALESCE((SELECT MAX(id) FROM employee_holiday_requests), 0) + 1, false);
SELECT setval('employee_daily_work_logs_id_seq', COALESCE((SELECT MAX(id) FROM employee_daily_work_logs), 0) + 1, false);
SELECT setval('expenses_id_seq', COALESCE((SELECT MAX(id) FROM expenses), 0) + 1, false);
SELECT setval('expense_categories_id_seq', COALESCE((SELECT MAX(id) FROM expense_categories), 0) + 1, false);
SELECT setval('investors_id_seq', COALESCE((SELECT MAX(id) FROM investors), 0) + 1, false);
SELECT setval('investments_id_seq', COALESCE((SELECT MAX(id) FROM investments), 0) + 1, false);
SELECT setval('notifications_id_seq', COALESCE((SELECT MAX(id) FROM notifications), 0) + 1, false);
SELECT setval('follow_ups_id_seq', COALESCE((SELECT MAX(id) FROM follow_ups), 0) + 1, false);
SELECT setval('permissions_id_seq', COALESCE((SELECT MAX(id) FROM permissions), 0) + 1, false);
SELECT setval('roles_id_seq', COALESCE((SELECT MAX(id) FROM roles), 0) + 1, false);
SELECT setval('activity_log_id_seq', COALESCE((SELECT MAX(id) FROM activity_log), 0) + 1, false);
SELECT setval('activity_logs_id_seq', COALESCE((SELECT MAX(id) FROM activity_logs), 0) + 1, false);
SELECT setval('reports_id_seq', COALESCE((SELECT MAX(id) FROM reports), 0) + 1, false);
SELECT setval('settings_id_seq', COALESCE((SELECT MAX(id) FROM settings), 0) + 1, false);
SELECT setval('setup_wizard_id_seq', COALESCE((SELECT MAX(id) FROM setup_wizard), 0) + 1, false);
```

---

### Step 7: Laravel `.env` Update Karo

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-db-password
```

**⚠️ Supabase mein yeh details kahan milegi:**
1. Supabase Dashboard → **Settings** → **Database**
2. **Connection string** section mein "URI" copy karo
3. Ya individual fields se fill karo

---

### Step 8: Verify Karo

```bash
# Laravel se test karo
php artisan migrate:status

# Saari migrations "Ran" dikhni chahiye
```

---

## 🚨 Common Issues & Fixes

| Issue | Fix |
|---|---|
| `relation already exists` | Schema pehle se run ho chuka hai, skip karo |
| `duplicate key value` | Seed data pehle se hai, `ON CONFLICT DO NOTHING` handle karega |
| `sequence not owned` | Sequences reset karo (Step 6) |
| `SSL required` | `.env` mein add karo: `DB_SSLMODE=require` |
| `ENUM type error` | Schema mein CHECK constraint use kiya hai, yeh issue nahi aayega |
| `password_reset_tokens error` | `email` column primary key hai, ensure karo |
