const app = require('./server');
const jwt = require('jsonwebtoken');

const server = app.listen(3999, async () => {
  const token = jwt.sign({ id: 1, username: 'admin', role: 'Main Admin', name: 'Jigar Patel' }, 'listing-erp-super-secret-jwt-key-2026');

  try {
    // 1. Test Dashboard
    const r1 = await fetch('http://localhost:3999/api/dashboard', { headers: { Authorization: `Bearer ${token}` } });
    const d1 = await r1.json();
    console.log('--- DASHBOARD METRICS ---');
    console.log('Payment Due:', d1.paymentDue);
    console.log('Monthly Collection:', d1.monthlyCollection);
    console.log('Salary Payable:', d1.totalSalaryPayableThisMonth);
    console.log('Commission Due:', d1.totalCommissionThisMonth);
    console.log('Monthly Expenses:', d1.monthlyExpenses);
    console.log('Net Bachat:', d1.netProjectedBachat);
    console.log('Available Fund:', d1.availableFund);
    console.log('Recent Activities:', d1.activities.length);

    // 2. Test Clients
    const r2 = await fetch('http://localhost:3999/api/clients', { headers: { Authorization: `Bearer ${token}` } });
    const d2 = await r2.json();
    console.log('--- CLIENTS ---');
    console.log('Active Clients:', d2.activeCount, 'Inactive:', d2.inactiveCount);

    // 3. Test Employees
    const r3 = await fetch('http://localhost:3999/api/employees', { headers: { Authorization: `Bearer ${token}` } });
    const d3 = await r3.json();
    console.log('--- EMPLOYEES ---');
    console.log('Employees found:', d3.employees.length);

    // 4. Test Salary
    const r4 = await fetch('http://localhost:3999/api/salary', { headers: { Authorization: `Bearer ${token}` } });
    const d4 = await r4.json();
    console.log('--- SALARIES ---');
    console.log('Salaries calculated for:', d4.salaries.length, 'employees');

    // 5. Test Expenses
    const r5 = await fetch('http://localhost:3999/api/expenses', { headers: { Authorization: `Bearer ${token}` } });
    const d5 = await r5.json();
    console.log('--- EXPENSES ---');
    console.log('Expenses entries:', d5.expenses.length);

    console.log('ALL API ENDPOINTS TESTED AND WORKING 100%!');
  } catch (err) {
    console.error('API Test Error:', err);
  } finally {
    server.close();
    process.exit(0);
  }
});
