-- ============================================================
-- Listing ERP — Live Data Export Imported into Supabase
-- Imported: 2026-08-25
-- ============================================================

-- Table: users (6 rows)
INSERT INTO users (id, name, username, email, phone, email_verified_at, password, avatar, must_change_password, status, remember_token, created_at, updated_at, deleted_at) VALUES 
(1, 'Jigar Patel', 'admin', 'admin@example.com', NULL, NULL, '$2y$12$n6YvLPwvuQptje3oFohlTe03hcVOn3xb2tOfThblKllKjSNSv16aS', NULL, FALSE, 'active', 'sqDaP0sdqWtSxZTFcI6FFKBuHyKDCXdwlvZNbX9rrWedFs1GXOYfZkmwRR3W', '2026-07-14 12:43:53', '2026-07-14 12:43:53', NULL),
(3, 'Limited Admin', 'limitedadmin', 'limited@example.com', NULL, NULL, '$2y$12$teOSjPObGvrfl4PO.9fXweEpncTfzP2tgqTt.mLnOv6jdt3RrxUHa', NULL, FALSE, 'active', NULL, '2026-07-14 17:08:44', '2026-07-14 17:09:46', NULL),
(4, 'KEVAL DESAI', 'keval', '-', NULL, NULL, '$2y$12$t30TaD8mWjx8IHm7F5Dd2./hkznOWAYEnTXOazupYW/R560s/FrqW', NULL, TRUE, 'active', NULL, '2026-07-14 17:34:39', '2026-07-14 17:34:39', NULL),
(5, 'Jaybhai', 'jay', '', NULL, NULL, '$2y$12$6YoryZ3Rt6ZgXEbqf8kp8.Td6Cf21rFUtJBa9mQLH2.vFxYZGTHVe', NULL, FALSE, 'active', 'eTATS39nxs2cd9T0lWQAo3wqvnqacYn4ntVh3i4RE8NbmKmIVCaR0eE5h4Wy', '2026-07-14 17:36:55', '2026-07-14 18:39:01', NULL),
(6, 'Keyur', 'Keyur', 'Keyur@listing.com', NULL, NULL, '$2y$12$KdNbA7FIQVoPVSQeY1WYFuTpKJ8Zkz9ZCth2.MYVW746d717LoFMO', NULL, FALSE, 'active', NULL, '2026-07-14 17:43:57', '2026-07-14 20:47:57', NULL),
(8, 'Yago', 'Yago', 'Yago@listingerp.local', '97245 74407', NULL, '$2y$12$AV4wbEyhge7.ywLpzUF.beAUYi4p4it2OsETpwLAOXM1oQW9n2hB2', NULL, FALSE, 'active', '6xPLm2GDH4WCz92HlGKJOK0MKwwwU1jUw3dhhcV0fYxYFSjHpwe6B42IcbHm', '2026-07-14 19:17:55', '2026-07-14 19:17:55', NULL)
ON CONFLICT (id) DO NOTHING;

-- Table: employees (3 rows)
INSERT INTO employees (id, user_id, name, phone, joining_date, role_title, salary_type, fixed_salary, commission_type, commission_value, status, created_at, updated_at, deleted_at) VALUES 
(1, 8, 'Yago', '97245 74407', '2026-07-14', 'Listing Manager', 'package_based', 0.00, 'percentage', 35.00, 'active', '2026-07-14 16:55:58', '2026-07-14 19:17:55', NULL),
(2, NULL, 'Hetnash', '8799103262', '2026-08-04', 'FLIPKART LISTING', 'both', 0.00, 'percentage', 30.00, 'active', '2026-08-03 18:58:47', '2026-08-03 18:58:47', NULL),
(3, NULL, 'JAY BHAI', '9328690772', '2026-08-04', 'MEESHO MANAGER', 'both', 0.00, 'percentage', 30.00, 'active', '2026-08-03 19:00:11', '2026-08-03 19:00:11', NULL)
ON CONFLICT (id) DO NOTHING;

-- Table: clients (7 rows)
INSERT INTO clients (id, name, mobile, mobile_secondary, email, joining_date, service_start_date, current_package, current_flipkart_gst, current_meesho_gst, work_location, manager_id, assigned_employee_id, address, status, created_by, created_at, updated_at, deleted_at) VALUES 
(1, 'Madhav Fashion', '83207 03511', NULL, '-', '2026-07-01', '2026-07-02', 11000.00, 0, 0, 'client_office', NULL, 1, NULL, 'active', 1, '2026-07-14 17:22:56', '2026-07-14 17:22:56', NULL),
(2, 'jaydeep bhai katargam', '81283 54765', NULL, '-', '2026-07-04', '2026-07-05', 10500.00, 0, 0, 'our_office', NULL, NULL, NULL, 'inactive', 1, '2026-07-14 17:24:55', '2026-08-12 12:56:20', NULL),
(3, 'mogal enterprice (vijay bhai)', '9998351849', '8160720785', '-', '2026-07-11', '2026-07-11', 4500.00, 1, 1, 'our_office', NULL, NULL, NULL, 'inactive', 1, '2026-07-14 17:25:52', '2026-08-12 12:56:06', NULL),
(4, 'JAY BHAI', '9328690772', NULL, 'araynbambharoliya706@gmail.com', '2026-07-18', '2026-07-18', 1000.00, 0, 3, 'our_office', NULL, NULL, NULL, 'inactive', 1, '2026-07-18 17:01:47', '2026-08-24 10:08:50', NULL),
(5, 'Kushikbhai mota varachha', '9512951275', NULL, 'Kushikbhaimotavarachha@gmail.com', '2026-08-03', '2026-08-04', 17000.00, 3, 0, 'our_office', NULL, 2, NULL, 'inactive', 1, '2026-08-03 19:04:10', '2026-08-24 10:10:06', NULL),
(6, 'Jenish Vora', '8140362987', NULL, 'jenishvora@gmail.com', '2026-08-01', '2026-08-01', 8000.00, 0, 1, 'our_office', NULL, 1, NULL, 'active', 1, '2026-08-07 11:36:03', '2026-08-07 11:36:03', NULL),
(7, 'Manish bhai', '97277 79904', NULL, 'manishbhai@gmail.com', '2026-08-24', '2026-08-20', 4500.00, 1, 0, 'our_office', NULL, 1, NULL, 'active', 1, '2026-08-24 10:07:59', '2026-08-24 10:07:59', NULL)
ON CONFLICT (id) DO NOTHING;

-- Reset Sequences
SELECT setval('users_id_seq', COALESCE((SELECT MAX(id) FROM users), 0) + 1, false);
SELECT setval('employees_id_seq', COALESCE((SELECT MAX(id) FROM employees), 0) + 1, false);
SELECT setval('clients_id_seq', COALESCE((SELECT MAX(id) FROM clients), 0) + 1, false);
SELECT setval('client_billing_cycles_id_seq', COALESCE((SELECT MAX(id) FROM client_billing_cycles), 0) + 1, false);
SELECT setval('client_payments_id_seq', COALESCE((SELECT MAX(id) FROM client_payments), 0) + 1, false);
SELECT setval('client_payment_ledger_id_seq', COALESCE((SELECT MAX(id) FROM client_payment_ledger), 0) + 1, false);
SELECT setval('client_accounts_id_seq', COALESCE((SELECT MAX(id) FROM client_accounts), 0) + 1, false);
SELECT setval('client_documents_id_seq', COALESCE((SELECT MAX(id) FROM client_documents), 0) + 1, false);
SELECT setval('client_notes_id_seq', COALESCE((SELECT MAX(id) FROM client_notes), 0) + 1, false);
SELECT setval('client_timeline_id_seq', COALESCE((SELECT MAX(id) FROM client_timeline), 0) + 1, false);
SELECT setval('client_package_history_id_seq', COALESCE((SELECT MAX(id) FROM client_package_history), 0) + 1, false);
SELECT setval('client_gst_history_id_seq', COALESCE((SELECT MAX(id) FROM client_gst_history), 0) + 1, false);
SELECT setval('client_manager_history_id_seq', COALESCE((SELECT MAX(id) FROM client_manager_history), 0) + 1, false);
SELECT setval('employee_client_assignments_id_seq', COALESCE((SELECT MAX(id) FROM employee_client_assignments), 0) + 1, false);
SELECT setval('employee_commissions_id_seq', COALESCE((SELECT MAX(id) FROM employee_commissions), 0) + 1, false);
SELECT setval('employee_salaries_id_seq', COALESCE((SELECT MAX(id) FROM employee_salaries), 0) + 1, false);
SELECT setval('employee_advances_id_seq', COALESCE((SELECT MAX(id) FROM employee_advances), 0) + 1, false);
SELECT setval('expenses_id_seq', COALESCE((SELECT MAX(id) FROM expenses), 0) + 1, false);
SELECT setval('investors_id_seq', COALESCE((SELECT MAX(id) FROM investors), 0) + 1, false);
SELECT setval('investments_id_seq', COALESCE((SELECT MAX(id) FROM investments), 0) + 1, false);
SELECT setval('notifications_id_seq', COALESCE((SELECT MAX(id) FROM notifications), 0) + 1, false);
SELECT setval('follow_ups_id_seq', COALESCE((SELECT MAX(id) FROM follow_ups), 0) + 1, false);
SELECT setval('employee_advance_requests_id_seq', COALESCE((SELECT MAX(id) FROM employee_advance_requests), 0) + 1, false);
SELECT setval('employee_holiday_requests_id_seq', COALESCE((SELECT MAX(id) FROM employee_holiday_requests), 0) + 1, false);
SELECT setval('employee_daily_work_logs_id_seq', COALESCE((SELECT MAX(id) FROM employee_daily_work_logs), 0) + 1, false);
SELECT setval('activity_log_id_seq', COALESCE((SELECT MAX(id) FROM activity_log), 0) + 1, false);
