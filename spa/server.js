const express = require('express');
const cors = require('cors');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('./db');

const JWT_SECRET = process.env.JWT_SECRET || 'listing-erp-super-secret-jwt-key-2026';

const app = express();
app.use(cors());
app.use(express.json());

// Auth Middleware
const authenticate = (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Unauthorized: Token missing' });
  }
  const token = authHeader.split(' ')[1];
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded;
    next();
  } catch (err) {
    return res.status(401).json({ error: 'Unauthorized: Invalid token' });
  }
};

// ─── AUTHENTICATION ─────────────────────────────────────────────
app.post('/api/auth/login', async (req, res) => {
  try {
    const { login, password } = req.body;
    if (!login || !password) {
      return res.status(400).json({ error: 'Username/Email and password are required' });
    }

    const userRes = await db.query(
      `SELECT u.*, r.name as role_name 
       FROM users u
       LEFT JOIN model_has_roles mhr ON mhr.model_id = u.id AND mhr.model_type = 'App\\\\Models\\\\User'
       LEFT JOIN roles r ON r.id = mhr.role_id
       WHERE (u.username = $1 OR u.email = $1) AND u.status = 'active'
       LIMIT 1`,
      [login]
    );

    if (userRes.rows.length === 0) {
      return res.status(401).json({ error: 'Invalid credentials or inactive account.' });
    }

    const user = userRes.rows[0];
    const valid = await bcrypt.compare(password, user.password);
    if (!valid) {
      return res.status(401).json({ error: 'Invalid credentials or password.' });
    }

    const token = jwt.sign(
      { id: user.id, username: user.username, email: user.email, role: user.role_name || 'Admin', name: user.name },
      JWT_SECRET,
      { expiresIn: '30d' }
    );

    // Log activity
    await db.query(
      `INSERT INTO activity_log (log_name, description, causer_type, causer_id, created_at, updated_at)
       VALUES ('default', 'User logged in via Web SPA', 'App\\\\Models\\\\User', $1, NOW(), NOW())`,
      [user.id]
    ).catch(() => {});

    res.json({
      token,
      user: {
        id: user.id,
        name: user.name,
        username: user.username,
        email: user.email,
        role: user.role_name || 'Admin',
      },
    });
  } catch (err) {
    console.error('Login error:', err);
    res.status(500).json({ error: 'Internal server error: ' + err.message });
  }
});

app.get('/api/auth/me', authenticate, async (req, res) => {
  res.json({ user: req.user });
});

// ─── DASHBOARD ──────────────────────────────────────────────────
app.get('/api/dashboard', authenticate, async (req, res) => {
  try {
    const now = new Date();
    const currentMonth = now.getMonth() + 1;
    const currentYear = now.getFullYear();
    const startOfMonth = `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`;
    const endOfMonth = new Date(currentYear, currentMonth, 0).toISOString().split('T')[0];

    // 1. Kitna Paisa Lena Hai (Pending balance of active clients)
    const dueRes = await db.query(
      `SELECT COALESCE(SUM(cbc.balance), 0) as payment_due
       FROM client_billing_cycles cbc
       JOIN clients c ON c.id = cbc.client_id
       WHERE c.status = 'active' AND cbc.status IN ('pending', 'partial', 'overdue')`
    );
    const paymentDue = parseFloat(dueRes.rows[0].payment_due || 0);

    const activeDueCountRes = await db.query(
      `SELECT COUNT(DISTINCT c.id) as count
       FROM clients c
       JOIN client_billing_cycles cbc ON cbc.client_id = c.id
       WHERE c.status = 'active' AND cbc.status IN ('pending', 'partial', 'overdue') AND cbc.balance > 0`
    );
    const activeDueClientsCount = parseInt(activeDueCountRes.rows[0].count || 0);

    // 2. This Month & Today Collection
    const monthlyColRes = await db.query(
      `SELECT COALESCE(SUM(amount), 0) as total FROM client_payments WHERE payment_date >= $1 AND payment_date <= $2`,
      [startOfMonth, endOfMonth]
    );
    const monthlyCollection = parseFloat(monthlyColRes.rows[0].total || 0);

    const todayStr = now.toISOString().split('T')[0];
    const todayColRes = await db.query(
      `SELECT COALESCE(SUM(amount), 0) as total FROM client_payments WHERE payment_date = $1`,
      [todayStr]
    );
    const todayCollection = parseFloat(todayColRes.rows[0].total || 0);

    // 3. Salary & Commission Me Kitna Dena Hai
    const empRes = await db.query(
      `SELECT e.*, 
        (SELECT COALESCE(SUM(amount - deducted_amount), 0) FROM employee_advances WHERE employee_id = e.id AND status = 'active') as pending_advance,
        (SELECT COALESCE(SUM(amount), 0) FROM employee_salary_deductions WHERE employee_id = e.id AND month = $1 AND year = $2) as month_deductions
       FROM employees e
       WHERE e.status = 'active'`,
      [currentMonth, currentYear]
    );

    let totalSalaryPayableThisMonth = 0;
    let totalCommissionThisMonth = 0;

    for (const emp of empRes.rows) {
      const salCheck = await db.query(
        `SELECT * FROM employee_salaries WHERE employee_id = $1 AND month = $2 AND year = $3 LIMIT 1`,
        [emp.id, currentMonth, currentYear]
      );

      if (salCheck.rows.length > 0) {
        totalSalaryPayableThisMonth += parseFloat(salCheck.rows[0].net_payable || 0);
        totalCommissionThisMonth += parseFloat(salCheck.rows[0].total_commission || 0);
      } else {
        // Calculate commission
        const commRes = await db.query(
          `SELECT COALESCE(SUM(
            CASE 
              WHEN eca.custom_commission_type = 'percentage' THEN (c.current_package * eca.custom_commission_value / 100)
              WHEN eca.custom_commission_type = 'fixed' THEN eca.custom_commission_value
              WHEN e.commission_type = 'percentage' THEN (c.current_package * e.commission_value / 100)
              WHEN e.commission_type = 'fixed' THEN e.commission_value
              ELSE 0
            END
          ), 0) as comm
          FROM employee_client_assignments eca
          JOIN clients c ON c.id = eca.client_id AND c.status = 'active'
          JOIN employees e ON e.id = eca.employee_id
          WHERE eca.employee_id = $1 AND eca.status = 'active'`,
          [emp.id]
        );
        const comm = parseFloat(commRes.rows[0].comm || 0);
        const base = ['fixed', 'both'].includes(emp.salary_type) ? parseFloat(emp.fixed_salary || 0) : 0;
        const commInSalary = ['package_based', 'both'].includes(emp.salary_type) ? comm : 0;
        const pendingAdv = parseFloat(emp.pending_advance || 0);
        const deductions = parseFloat(emp.month_deductions || 0);
        const gross = base + commInSalary;
        const advDeduct = Math.min(gross, pendingAdv);
        const net = Math.max(0, gross - advDeduct - deductions);

        totalSalaryPayableThisMonth += net;
        totalCommissionThisMonth += comm;
      }
    }

    // 4. Monthly Deductible Expenses
    const expRes = await db.query(
      `SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date >= $1 AND expense_date <= $2 AND include_in_calculation = true`,
      [startOfMonth, endOfMonth]
    );
    const monthlyExpenses = parseFloat(expRes.rows[0].total || 0);

    const allExpensesRes = await db.query(
      `SELECT ex.*, ec.name as category_name, u.name as created_by_name
       FROM expenses ex
       LEFT JOIN expense_categories ec ON ec.id = ex.category_id
       LEFT JOIN users u ON u.id = ex.created_by
       WHERE ex.expense_date >= $1 AND ex.expense_date <= $2
       ORDER BY ex.expense_date DESC LIMIT 50`,
      [startOfMonth, endOfMonth]
    );

    // 5. Net Projected Savings
    const netProjectedBachat = paymentDue - totalSalaryPayableThisMonth - monthlyExpenses;

    // 6. Available Fund
    const totCol = parseFloat((await db.query(`SELECT COALESCE(SUM(amount), 0) as t FROM client_payments`)).rows[0].t);
    const totExp = parseFloat((await db.query(`SELECT COALESCE(SUM(amount), 0) as t FROM expenses`)).rows[0].t);
    const totAdv = parseFloat((await db.query(`SELECT COALESCE(SUM(amount), 0) as t FROM employee_advances`)).rows[0].t);
    const totSal = parseFloat((await db.query(`SELECT COALESCE(SUM(paid_amount), 0) as t FROM employee_salaries`)).rows[0].t);
    const availableFund = totCol - totExp - totAdv - totSal;

    // Recent Activities
    const actRes = await db.query(
      `SELECT a.*, u.name as user_name FROM activity_log a LEFT JOIN users u ON u.id = a.causer_id ORDER BY a.created_at DESC LIMIT 8`
    );

    // Upcoming renewals
    const renewRes = await db.query(
      `SELECT cbc.*, c.name as client_name, c.mobile as client_mobile
       FROM client_billing_cycles cbc
       JOIN clients c ON c.id = cbc.client_id
       WHERE c.status = 'active' AND cbc.end_date >= CURRENT_DATE AND cbc.end_date <= CURRENT_DATE + INTERVAL '7 days'
       ORDER BY cbc.end_date ASC LIMIT 5`
    );

    res.json({
      paymentDue,
      activeDueClientsCount,
      monthlyCollection,
      todayCollection,
      totalSalaryPayableThisMonth,
      totalCommissionThisMonth,
      monthlyExpenses,
      netProjectedBachat,
      availableFund,
      expenses: allExpensesRes.rows,
      activities: actRes.rows,
      upcomingRenewals: renewRes.rows,
    });
  } catch (err) {
    console.error('Dashboard error:', err);
    res.status(500).json({ error: err.message });
  }
});

// ─── CLIENTS MODULE ─────────────────────────────────────────────
app.get('/api/clients', authenticate, async (req, res) => {
  try {
    const clientsRes = await db.query(`
      SELECT c.*,
        u.name as manager_name,
        (SELECT COALESCE(SUM(balance), 0) FROM client_billing_cycles WHERE client_id = c.id AND status IN ('pending', 'partial', 'overdue')) as total_due,
        (SELECT json_agg(json_build_object('id', e.id, 'name', e.name)) 
         FROM employee_client_assignments eca 
         JOIN employees e ON e.id = eca.employee_id 
         WHERE eca.client_id = c.id AND eca.status = 'active') as assigned_employees
      FROM clients c
      LEFT JOIN users u ON u.id = c.manager_id
      ORDER BY c.id DESC
    `);

    const activeCount = clientsRes.rows.filter(c => c.status === 'active').length;
    const inactiveCount = clientsRes.rows.filter(c => c.status === 'inactive').length;

    res.json({
      clients: clientsRes.rows,
      activeCount,
      inactiveCount,
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/clients/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const clientRes = await db.query(`
      SELECT c.*, u.name as manager_name,
        (SELECT COALESCE(SUM(balance), 0) FROM client_billing_cycles WHERE client_id = c.id AND status IN ('pending', 'partial', 'overdue')) as total_due
      FROM clients c
      LEFT JOIN users u ON u.id = c.manager_id
      WHERE c.id = $1 LIMIT 1
    `, [id]);

    if (clientRes.rows.length === 0) return res.status(404).json({ error: 'Client not found' });
    const client = clientRes.rows[0];

    const cycles = (await db.query(`SELECT * FROM client_billing_cycles WHERE client_id = $1 ORDER BY start_date DESC`, [id])).rows;
    const payments = (await db.query(`SELECT cp.*, u.name as received_by_name FROM client_payments cp LEFT JOIN users u ON u.id = cp.received_by WHERE cp.client_id = $1 ORDER BY cp.payment_date DESC`, [id])).rows;
    const assignments = (await db.query(`
      SELECT eca.*, e.name as employee_name, e.phone as employee_phone 
      FROM employee_client_assignments eca
      JOIN employees e ON e.id = eca.employee_id
      WHERE eca.client_id = $1
      ORDER BY eca.assigned_date DESC
    `, [id])).rows;

    res.json({ client, cycles, payments, assignments });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/clients', authenticate, async (req, res) => {
  try {
    const { name, mobile, mobile_secondary, email, address, work_location, joining_date, service_start_date, current_package, gst_count, manager_id, assigned_employee_ids } = req.body;

    const insertRes = await db.query(
      `INSERT INTO clients (name, mobile, mobile_secondary, email, address, work_location, joining_date, service_start_date, current_package, gst_count, manager_id, status, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, 'active', NOW(), NOW()) RETURNING *`,
      [name, mobile, mobile_secondary || null, email || null, address || null, work_location || null, joining_date || new Date(), service_start_date || new Date(), current_package || 0, gst_count || 1, manager_id || null]
    );
    const client = insertRes.rows[0];

    // Create initial billing cycle
    const startDate = service_start_date || new Date().toISOString().split('T')[0];
    const endDate = new Date(new Date(startDate).setMonth(new Date(startDate).getMonth() + 1)).toISOString().split('T')[0];
    await db.query(
      `INSERT INTO client_billing_cycles (client_id, start_date, end_date, package_amount, total_amount, paid_amount, balance, status, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $4, 0, $4, 'pending', NOW(), NOW())`,
      [client.id, startDate, endDate, current_package || 0]
    );

    // Assign employees
    if (Array.isArray(assigned_employee_ids)) {
      for (const empId of assigned_employee_ids) {
        await db.query(
          `INSERT INTO employee_client_assignments (employee_id, client_id, assigned_date, status, created_at, updated_at)
           VALUES ($1, $2, $3, 'active', NOW(), NOW())`,
          [empId, client.id, startDate]
        );
      }
    }

    res.json({ success: true, client });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/clients/:id/renew', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const { package_option, custom_package_amount, start_date, end_date, collect_payment, payment_amount, payment_method, notes } = req.body;

    const clientRes = await db.query(`SELECT * FROM clients WHERE id = $1`, [id]);
    if (clientRes.rows.length === 0) return res.status(404).json({ error: 'Client not found' });
    const client = clientRes.rows[0];

    const packageAmount = package_option === 'new' && custom_package_amount ? parseFloat(custom_package_amount) : parseFloat(client.current_package);

    if (package_option === 'new') {
      await db.query(`UPDATE clients SET current_package = $1, updated_at = NOW() WHERE id = $2`, [packageAmount, id]);
    }

    // Insert new billing cycle
    const cycleRes = await db.query(
      `INSERT INTO client_billing_cycles (client_id, start_date, end_date, package_amount, total_amount, paid_amount, balance, status, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $4, 0, $4, 'pending', NOW(), NOW()) RETURNING *`,
      [id, start_date, end_date, packageAmount]
    );
    const newCycle = cycleRes.rows[0];

    // Collect payment if requested
    if (collect_payment && payment_amount > 0) {
      const pAmt = parseFloat(payment_amount);
      await db.query(
        `INSERT INTO client_payments (client_id, billing_cycle_id, amount, payment_date, payment_method, notes, received_by, created_at, updated_at)
         VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), NOW())`,
        [id, newCycle.id, pAmt, start_date || new Date().toISOString().split('T')[0], payment_method || 'cash', notes || 'Collected during renewal', req.user.id]
      );

      const newPaid = pAmt;
      const newBal = Math.max(0, packageAmount - newPaid);
      const newStatus = newBal === 0 ? 'paid' : (newPaid > 0 ? 'partial' : 'pending');

      await db.query(
        `UPDATE client_billing_cycles SET paid_amount = $1, balance = $2, status = $3, updated_at = NOW() WHERE id = $4`,
        [newPaid, newBal, newStatus, newCycle.id]
      );
    }

    res.json({ success: true, message: 'Client renewed successfully!' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/clients/:id/payments', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const { amount, payment_date, payment_method, notes } = req.body;
    let remainingPayment = parseFloat(amount);

    // Fetch pending cycles
    const pendingCycles = (await db.query(
      `SELECT * FROM client_billing_cycles WHERE client_id = $1 AND balance > 0 ORDER BY start_date ASC`,
      [id]
    )).rows;

    let targetCycleId = pendingCycles.length > 0 ? pendingCycles[0].id : null;

    await db.query(
      `INSERT INTO client_payments (client_id, billing_cycle_id, amount, payment_date, payment_method, notes, received_by, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), NOW())`,
      [id, targetCycleId, amount, payment_date || new Date().toISOString().split('T')[0], payment_method || 'cash', notes || null, req.user.id]
    );

    for (const cycle of pendingCycles) {
      if (remainingPayment <= 0) break;
      const deduct = Math.min(parseFloat(cycle.balance), remainingPayment);
      const newPaid = parseFloat(cycle.paid_amount) + deduct;
      const newBalance = parseFloat(cycle.balance) - deduct;
      const newStatus = newBalance === 0 ? 'paid' : 'partial';

      await db.query(
        `UPDATE client_billing_cycles SET paid_amount = $1, balance = $2, status = $3, updated_at = NOW() WHERE id = $4`,
        [newPaid, newBalance, newStatus, cycle.id]
      );
      remainingPayment -= deduct;
    }

    res.json({ success: true, message: 'Payment recorded successfully!' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.put('/api/clients/:id/status', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const { status } = req.body;
    await db.query(`UPDATE clients SET status = $1, updated_at = NOW() WHERE id = $2`, [status, id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/clients/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const client = (await db.query(`SELECT * FROM clients WHERE id = $1`, [id])).rows[0];
    if (!client) return res.status(404).json({ error: 'Client not found' });
    if (client.status !== 'inactive') return res.status(400).json({ error: 'Only inactive clients can be deleted.' });

    await db.query(`DELETE FROM client_payments WHERE client_id = $1`, [id]);
    await db.query(`DELETE FROM client_billing_cycles WHERE client_id = $1`, [id]);
    await db.query(`DELETE FROM employee_client_assignments WHERE client_id = $1`, [id]);
    await db.query(`DELETE FROM clients WHERE id = $1`, [id]);

    res.json({ success: true, message: 'Inactive client deleted successfully.' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ─── EMPLOYEES & SALARY ─────────────────────────────────────────
app.get('/api/employees', authenticate, async (req, res) => {
  try {
    const empRes = await db.query(`
      SELECT e.*, u.username as user_login,
        (SELECT COUNT(*) FROM employee_client_assignments WHERE employee_id = e.id AND status = 'active') as active_clients_count,
        (SELECT COALESCE(SUM(amount - deducted_amount), 0) FROM employee_advances WHERE employee_id = e.id AND status = 'active') as pending_advance
      FROM employees e
      LEFT JOIN users u ON u.id = e.user_id
      ORDER BY e.id DESC
    `);
    res.json({ employees: empRes.rows });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/employees', authenticate, async (req, res) => {
  try {
    const { name, phone, email, joining_date, role_title, salary_type, fixed_salary, commission_type, commission_value, username, password } = req.body;

    let userId = null;
    if (username && password) {
      const hashed = await bcrypt.hash(password, 10);
      const userRes = await db.query(
        `INSERT INTO users (name, username, email, password, status, created_at, updated_at)
         VALUES ($1, $2, $3, $4, 'active', NOW(), NOW()) RETURNING id`,
        [name, username, email || `${username}@listingerp.local`, hashed]
      );
      userId = userRes.rows[0].id;

      // Assign role
      const roleRes = await db.query(`SELECT id FROM roles WHERE name = 'Employee' LIMIT 1`);
      if (roleRes.rows.length > 0) {
        await db.query(
          `INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES ($1, 'App\\\\Models\\\\User', $2)`,
          [roleRes.rows[0].id, userId]
        );
      }
    }

    const empRes = await db.query(
      `INSERT INTO employees (user_id, name, phone, email, joining_date, role_title, salary_type, fixed_salary, commission_type, commission_value, status, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, 'active', NOW(), NOW()) RETURNING *`,
      [userId, name, phone, email || null, joining_date || new Date(), role_title || 'Executive', salary_type || 'fixed', fixed_salary || 0, commission_type || 'fixed', commission_value || 0]
    );

    res.json({ success: true, employee: empRes.rows[0] });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/salary', authenticate, async (req, res) => {
  try {
    const now = new Date();
    const month = parseInt(req.query.month) || (now.getMonth() + 1);
    const year = parseInt(req.query.year) || now.getFullYear();

    const employees = (await db.query(`SELECT * FROM employees WHERE status = 'active' ORDER BY name ASC`)).rows;
    const salaryList = [];

    for (const emp of employees) {
      const salCheck = await db.query(
        `SELECT * FROM employee_salaries WHERE employee_id = $1 AND month = $2 AND year = $3 LIMIT 1`,
        [emp.id, month, year]
      );

      const deductionsRes = await db.query(
        `SELECT * FROM employee_salary_deductions WHERE employee_id = $1 AND month = $2 AND year = $3 ORDER BY id DESC`,
        [emp.id, month, year]
      );
      const totalDeductions = deductionsRes.rows.reduce((sum, d) => sum + parseFloat(d.amount), 0);

      const advancesRes = await db.query(
        `SELECT * FROM employee_advances WHERE employee_id = $1 AND status = 'active' ORDER BY id DESC`,
        [emp.id]
      );
      const pendingAdvance = advancesRes.rows.reduce((sum, a) => sum + parseFloat(a.amount - a.deducted_amount), 0);

      if (salCheck.rows.length > 0) {
        salaryList.push({
          ...salCheck.rows[0],
          employee: emp,
          deductions: deductionsRes.rows,
          total_deductions: totalDeductions,
          pending_advance: pendingAdvance,
        });
      } else {
        // Calculate commission
        const commRes = await db.query(
          `SELECT COALESCE(SUM(
            CASE 
              WHEN eca.custom_commission_type = 'percentage' THEN (c.current_package * eca.custom_commission_value / 100)
              WHEN eca.custom_commission_type = 'fixed' THEN eca.custom_commission_value
              WHEN e.commission_type = 'percentage' THEN (c.current_package * e.commission_value / 100)
              WHEN e.commission_type = 'fixed' THEN e.commission_value
              ELSE 0
            END
          ), 0) as comm
          FROM employee_client_assignments eca
          JOIN clients c ON c.id = eca.client_id AND c.status = 'active'
          JOIN employees e ON e.id = eca.employee_id
          WHERE eca.employee_id = $1 AND eca.status = 'active'`,
          [emp.id]
        );
        const comm = parseFloat(commRes.rows[0].comm || 0);
        const base = ['fixed', 'both'].includes(emp.salary_type) ? parseFloat(emp.fixed_salary || 0) : 0;
        const commInSalary = ['package_based', 'both'].includes(emp.salary_type) ? comm : 0;
        const gross = base + commInSalary;
        const advDeduct = Math.min(gross, pendingAdvance);
        const net = Math.max(0, gross - advDeduct - totalDeductions);

        salaryList.push({
          employee_id: emp.id,
          month,
          year,
          base_salary: base,
          total_commission: comm,
          advance_deducted: advDeduct,
          other_deductions: totalDeductions,
          net_payable: net,
          paid_amount: 0,
          status: 'pending',
          employee: emp,
          deductions: deductionsRes.rows,
          pending_advance: pendingAdvance,
        });
      }
    }

    res.json({ month, year, salaries: salaryList });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/employees/:id/deductions', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const { amount, reason, month, year } = req.body;
    const now = new Date();
    const dMonth = month || (now.getMonth() + 1);
    const dYear = year || now.getFullYear();

    const insertRes = await db.query(
      `INSERT INTO employee_salary_deductions (employee_id, month, year, amount, reason, created_by, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW()) RETURNING *`,
      [id, dMonth, dYear, amount, reason || 'Salary Penalty / Cut', req.user.id]
    );

    // Notify employee if user account exists
    const emp = (await db.query(`SELECT * FROM employees WHERE id = $1`, [id])).rows[0];
    if (emp && emp.user_id) {
      await db.query(
        `INSERT INTO notifications (user_id, title, message, type, is_read, created_at, updated_at)
         VALUES ($1, 'Salary Deduction Applied', $2, 'salary_deduction', false, NOW(), NOW())`,
        [emp.user_id, `₹${amount} has been deducted from your salary for ${dMonth}/${dYear}. Reason: ${reason}`]
      ).catch(() => {});
    }

    res.json({ success: true, deduction: insertRes.rows[0] });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/deductions/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    await db.query(`DELETE FROM employee_salary_deductions WHERE id = $1`, [id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/salary/advance', authenticate, async (req, res) => {
  try {
    const { employee_id, amount, reason, advance_date } = req.body;
    await db.query(
      `INSERT INTO employee_advances (employee_id, amount, deducted_amount, reason, advance_date, status, approved_by, created_at, updated_at)
       VALUES ($1, $2, 0, $3, $4, 'active', $5, NOW(), NOW())`,
      [employee_id, amount, reason || 'Salary Advance', advance_date || new Date().toISOString().split('T')[0], req.user.id]
    );
    res.json({ success: true, message: 'Advance disbursed successfully!' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/salary/pay', authenticate, async (req, res) => {
  try {
    const { employee_id, month, year, amount, payment_method, notes } = req.body;
    const salCheck = await db.query(
      `SELECT * FROM employee_salaries WHERE employee_id = $1 AND month = $2 AND year = $3 LIMIT 1`,
      [employee_id, month, year]
    );

    if (salCheck.rows.length > 0) {
      await db.query(
        `UPDATE employee_salaries SET paid_amount = $1, status = 'paid', paid_at = NOW(), payment_method = $2, notes = $3, updated_at = NOW()
         WHERE id = $4`,
        [amount, payment_method || 'cash', notes || null, salCheck.rows[0].id]
      );
    } else {
      await db.query(
        `INSERT INTO employee_salaries (employee_id, month, year, base_salary, total_commission, other_deductions, net_payable, paid_amount, status, payment_method, notes, paid_at, created_at, updated_at)
         VALUES ($1, $2, $3, $4, 0, 0, $4, $4, 'paid', $5, $6, NOW(), NOW(), NOW())`,
        [employee_id, month, year, amount, payment_method || 'cash', notes || null]
      );
    }
    res.json({ success: true, message: 'Salary marked as paid!' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ─── EXPENSES MODULE ────────────────────────────────────────────
app.get('/api/expenses', authenticate, async (req, res) => {
  try {
    const now = new Date();
    const month = parseInt(req.query.month) || (now.getMonth() + 1);
    const year = parseInt(req.query.year) || now.getFullYear();
    const startOfMonth = `${year}-${String(month).padStart(2, '0')}-01`;
    const endOfMonth = new Date(year, month, 0).toISOString().split('T')[0];

    const generalExp = (await db.query(`
      SELECT ex.*, ec.name as category_name, u.name as created_by_name, 'general' as entry_type
      FROM expenses ex
      LEFT JOIN expense_categories ec ON ec.id = ex.category_id
      LEFT JOIN users u ON u.id = ex.created_by
      WHERE ex.expense_date >= $1 AND ex.expense_date <= $2
      ORDER BY ex.expense_date DESC
    `, [startOfMonth, endOfMonth])).rows;

    const paidSalaries = (await db.query(`
      SELECT es.id, es.paid_amount as amount, es.paid_at as expense_date, es.payment_method, es.notes, 'Salary' as title, 'Salary Payout' as category_name, e.name as employee_name, 'salary' as entry_type, true as include_in_calculation
      FROM employee_salaries es
      JOIN employees e ON e.id = es.employee_id
      WHERE es.month = $1 AND es.year = $2 AND es.status = 'paid'
    `, [month, year])).rows;

    const advances = (await db.query(`
      SELECT ea.id, ea.amount, ea.advance_date as expense_date, ea.reason as notes, 'Advance' as title, 'Salary Advance' as category_name, e.name as employee_name, 'advance' as entry_type, true as include_in_calculation
      FROM employee_advances WHERE ea.advance_date >= $1 AND ea.advance_date <= $2
      JOIN employees e ON e.id = ea.employee_id
    `, [startOfMonth, endOfMonth]).catch(() => ({ rows: [] }))).rows;

    const allExpenses = [...generalExp, ...paidSalaries, ...advances];
    const totalGeneral = generalExp.reduce((s, e) => s + parseFloat(e.amount), 0);
    const totalSalaryPaid = paidSalaries.reduce((s, e) => s + parseFloat(e.amount), 0);
    const totalDeductible = generalExp.filter(e => e.include_in_calculation).reduce((s, e) => s + parseFloat(e.amount), 0);

    const categories = (await db.query(`SELECT * FROM expense_categories ORDER BY name ASC`)).rows;

    res.json({
      month,
      year,
      expenses: allExpenses,
      totalGeneral,
      totalSalaryPaid,
      totalDeductible,
      categories,
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/expenses', authenticate, async (req, res) => {
  try {
    const { category_id, title, amount, expense_date, expense_type, notes, include_in_calculation } = req.body;
    const insertRes = await db.query(
      `INSERT INTO expenses (category_id, title, amount, expense_date, expense_type, notes, include_in_calculation, created_by, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW(), NOW()) RETURNING *`,
      [category_id || null, title, amount, expense_date || new Date().toISOString().split('T')[0], expense_type || 'one_time', notes || null, include_in_calculation !== false, req.user.id]
    );
    res.json({ success: true, expense: insertRes.rows[0] });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.patch('/api/expenses/:id/toggle-calculation', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    const { include_in_calculation } = req.body;
    await db.query(`UPDATE expenses SET include_in_calculation = $1, updated_at = NOW() WHERE id = $2`, [include_in_calculation, id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/expenses/:id', authenticate, async (req, res) => {
  try {
    const { id } = req.params;
    await db.query(`DELETE FROM expenses WHERE id = $1`, [id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ─── WORK TRACKER ───────────────────────────────────────────────
app.get('/api/work-tracker', authenticate, async (req, res) => {
  try {
    const { start_date, end_date, employee_id } = req.query;
    let query = `
      SELECT eca.*, c.name as client_name, c.mobile as client_mobile, c.status as client_status, c.work_location,
             e.name as employee_name, e.phone as employee_phone
      FROM employee_client_assignments eca
      JOIN clients c ON c.id = eca.client_id
      JOIN employees e ON e.id = eca.employee_id
      WHERE 1=1
    `;
    const params = [];

    if (employee_id) {
      params.push(employee_id);
      query += ` AND eca.employee_id = $${params.length}`;
    }

    if (start_date) {
      params.push(start_date);
      query += ` AND (eca.assigned_date <= $${params.length} OR eca.assigned_date IS NULL)`;
    }

    query += ` ORDER BY eca.assigned_date DESC`;
    const assignments = (await db.query(query, params)).rows;

    const employees = (await db.query(`SELECT id, name FROM employees WHERE status = 'active' ORDER BY name ASC`)).rows;

    res.json({ assignments, employees });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = app;

if (require.main === module) {
  const PORT = process.env.PORT || 3000;
  app.listen(PORT, () => {
    console.log(`Listing ERP API Server running on http://localhost:${PORT}`);
  });
}
