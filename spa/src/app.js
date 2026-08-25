// ─── STATE MANAGEMENT ───────────────────────────────────────────
const state = {
  token: localStorage.getItem('erp_token') || null,
  user: JSON.parse(localStorage.getItem('erp_user') || 'null'),
  currentTab: 'dashboard',
  activeClientSubTab: 'active',
  dashboard: null,
  clients: [],
  activeClientsCount: 0,
  inactiveClientsCount: 0,
  clientSearchQuery: '',
  employees: [],
  salaries: [],
  salaryMonth: new Date().getMonth() + 1,
  salaryYear: new Date().getFullYear(),
  expenses: [],
  expenseMonth: new Date().getMonth() + 1,
  expenseYear: new Date().getFullYear(),
  expenseCategories: [],
  expenseTotals: { totalGeneral: 0, totalSalaryPaid: 0, totalDeductible: 0 },
  workTracker: [],
  activeModal: null, // 'addClient' | 'renewClient' | 'receivePayment' | 'addEmployee' | 'cutSalary' | 'giveAdvance' | 'addExpense' | 'deleteClient' | 'workHistory' | 'clientDetail'
  modalPayload: {},
  toast: null,
  loading: false,
};

// ─── API CLIENT ─────────────────────────────────────────────────
async function api(endpoint, options = {}) {
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  if (state.token) {
    headers['Authorization'] = `Bearer ${state.token}`;
  }

  try {
    const res = await fetch(endpoint, { ...options, headers });
    const data = await res.json();
    if (!res.ok) {
      if (res.status === 401) {
        logout();
      }
      throw new Error(data.error || 'Request failed');
    }
    return data;
  } catch (err) {
    showToast(err.message, 'error');
    throw err;
  }
}

// ─── TOAST NOTIFICATIONS ────────────────────────────────────────
function showToast(message, type = 'success') {
  state.toast = { message, type };
  render();
  setTimeout(() => {
    state.toast = null;
    render();
  }, 4000);
}

// ─── AUTH ACTIONS ───────────────────────────────────────────────
async function login(username, password) {
  state.loading = true;
  render();
  try {
    const res = await api('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ login: username, password }),
    });
    state.token = res.token;
    state.user = res.user;
    localStorage.setItem('erp_token', res.token);
    localStorage.setItem('erp_user', JSON.stringify(res.user));
    state.currentTab = 'dashboard';
    showToast(`Welcome back, ${res.user.name}!`);
    await loadInitialData();
  } catch (err) {
    // Toast already shown
  } finally {
    state.loading = false;
    render();
  }
}

function logout() {
  state.token = null;
  state.user = null;
  localStorage.removeItem('erp_token');
  localStorage.removeItem('erp_user');
  render();
}

// ─── DATA FETCHING ──────────────────────────────────────────────
async function loadInitialData() {
  if (!state.token) return;
  state.loading = true;
  render();
  try {
    if (state.currentTab === 'dashboard') {
      state.dashboard = await api('/api/dashboard');
    } else if (state.currentTab === 'clients') {
      const res = await api('/api/clients');
      state.clients = res.clients;
      state.activeClientsCount = res.activeCount;
      state.inactiveClientsCount = res.inactiveCount;
    } else if (state.currentTab === 'employees') {
      const res = await api('/api/employees');
      state.employees = res.employees;
    } else if (state.currentTab === 'salary') {
      const res = await api(`/api/salary?month=${state.salaryMonth}&year=${state.salaryYear}`);
      state.salaries = res.salaries;
    } else if (state.currentTab === 'expenses') {
      const res = await api(`/api/expenses?month=${state.expenseMonth}&year=${state.expenseYear}`);
      state.expenses = res.expenses;
      state.expenseCategories = res.categories;
      state.expenseTotals = {
        totalGeneral: res.totalGeneral,
        totalSalaryPaid: res.totalSalaryPaid,
        totalDeductible: res.totalDeductible,
      };
    } else if (state.currentTab === 'work-tracker') {
      const res = await api('/api/work-tracker');
      state.workTracker = res.assignments;
      state.employees = res.employees;
    }
  } catch (err) {
    console.error(err);
  } finally {
    state.loading = false;
    render();
  }
}

function switchTab(tab) {
  state.currentTab = tab;
  state.activeModal = null;
  loadInitialData();
}

// ─── HELPERS ────────────────────────────────────────────────────
function formatINR(val) {
  const num = parseFloat(val || 0);
  return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });
  } catch {
    return dateStr;
  }
}

// ─── RENDERERS ──────────────────────────────────────────────────
function render() {
  const app = document.getElementById('app');
  if (!state.token) {
    app.innerHTML = renderLoginPage();
    lucide.createIcons();
    attachLoginEvents();
    return;
  }

  app.innerHTML = `
    <div class="h-full flex flex-col md:flex-row overflow-hidden bg-slate-950">
      ${renderSidebar()}
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-900/50">
        ${renderHeader()}
        
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 space-y-6">
          ${renderTabContent()}
        </div>
      </main>

      ${renderModal()}
      ${renderToast()}
    </div>
  `;

  lucide.createIcons();
  attachEventListeners();
}

// ─── LOGIN PAGE ─────────────────────────────────────────────────
function renderLoginPage() {
  return `
    <div class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-indigo-950 via-slate-950 to-slate-900 relative overflow-hidden">
      <!-- Background Ambient Glow -->
      <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="w-full max-w-md relative z-10 glass-panel rounded-3xl p-8 shadow-2xl border border-slate-700/50">
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 text-indigo-400 mb-4 shadow-lg shadow-indigo-500/20">
            <i data-lucide="building-2" class="w-8 h-8"></i>
          </div>
          <h1 class="text-3xl font-extrabold text-white tracking-tight">Listing ERP</h1>
          <p class="text-sm text-slate-400 mt-1">Enterprise Management Platform</p>
          
          <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Supabase Database: Online / Connected</span>
          </div>
        </div>

        <form id="loginForm" class="space-y-5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Username or Email</label>
            <div class="relative">
              <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-4 top-3.5"></i>
              <input type="text" id="loginUsername" required value="admin" class="w-full pl-11 pr-4 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Enter username or email">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
            <div class="relative">
              <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-4 top-3.5"></i>
              <input type="password" id="loginPassword" required value="Admin@123" class="w-full pl-11 pr-4 py-3 bg-slate-900/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Enter password">
            </div>
          </div>

          <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer text-slate-300">
              <input type="checkbox" checked class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
              <span>Remember me</span>
            </label>
            <span class="text-indigo-400 hover:text-indigo-300 cursor-pointer">Quick Access</span>
          </div>

          <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
            ${state.loading ? '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Signing in...' : '<i data-lucide="log-in" class="w-4 h-4"></i> Sign In to Dashboard'}
          </button>
        </form>

        <p class="text-center text-slate-500 text-xs mt-6">© ${new Date().getFullYear()} Listing ERP • High-Speed Netlify SPA</p>
      </div>
    </div>
  `;
}

// ─── SIDEBAR & HEADER ───────────────────────────────────────────
function renderSidebar() {
  const navItems = [
    { id: 'dashboard', label: 'Executive Dashboard', icon: 'layout-dashboard' },
    { id: 'clients', label: 'Clients Hub', icon: 'users', badge: state.activeClientsCount || null },
    { id: 'employees', label: 'Employees', icon: 'user-check' },
    { id: 'salary', label: 'Salaries & Payouts', icon: 'wallet' },
    { id: 'expenses', label: 'Unified Expenses', icon: 'receipt' },
    { id: 'work-tracker', label: 'Work Tracker', icon: 'calendar-check' },
  ];

  return `
    <aside class="w-full md:w-64 lg:w-72 bg-slate-950 border-r border-slate-800/80 flex flex-col flex-shrink-0">
      <div class="p-6 flex items-center justify-between border-b border-slate-800/60">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-600/30">
            L
          </div>
          <div>
            <h2 class="font-bold text-white tracking-tight leading-none text-base">Listing ERP</h2>
            <span class="text-[11px] font-medium text-emerald-400 flex items-center gap-1 mt-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Fast Netlify SPA
            </span>
          </div>
        </div>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
        ${navItems.map(item => `
          <button onclick="window.switchTab('${item.id}')" 
                  class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all ${state.currentTab === item.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900'}">
            <div class="flex items-center gap-3">
              <i data-lucide="${item.icon}" class="w-4 h-4"></i>
              <span>${item.label}</span>
            </div>
            ${item.badge ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-700/50">${item.badge}</span>` : ''}
          </button>
        `).join('')}
      </nav>

      <div class="p-4 border-t border-slate-800/60">
        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">
              ${(state.user?.name || 'A').charAt(0)}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold text-white truncate">${state.user?.name || 'Admin'}</p>
              <p class="text-[10px] text-slate-400 truncate">${state.user?.role || 'Main Admin'}</p>
            </div>
          </div>
          <button onclick="window.logout()" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-all">
            <i data-lucide="log-out" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    </aside>
  `;
}

function renderHeader() {
  return `
    <header class="h-16 bg-slate-950/60 backdrop-blur-md border-b border-slate-800/60 px-6 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-3">
        <h1 class="text-lg font-bold text-white capitalize">
          ${state.currentTab.replace('-', ' ')}
        </h1>
      </div>

      <div class="flex items-center gap-3">
        <button onclick="window.openModal('addClient')" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition-all">
          <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Client
        </button>
        <button onclick="window.openModal('addExpense')" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-md shadow-amber-600/20 transition-all">
          <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Expense
        </button>
      </div>
    </header>
  `;
}

// ─── TAB CONTENT ROUTER ─────────────────────────────────────────
function renderTabContent() {
  if (state.loading && !state.dashboard && !state.clients.length) {
    return `
      <div class="h-64 flex items-center justify-center text-slate-400">
        <div class="flex flex-col items-center gap-3">
          <i data-lucide="loader" class="w-8 h-8 animate-spin text-indigo-500"></i>
          <p class="text-xs font-semibold">Loading data from Supabase...</p>
        </div>
      </div>
    `;
  }

  if (state.currentTab === 'dashboard') return renderDashboardView();
  if (state.currentTab === 'clients') return renderClientsView();
  if (state.currentTab === 'employees') return renderEmployeesView();
  if (state.currentTab === 'salary') return renderSalariesView();
  if (state.currentTab === 'expenses') return renderExpensesView();
  if (state.currentTab === 'work-tracker') return renderWorkTrackerView();
  return `<div>Tab not found</div>`;
}

// ─── VIEW 1: EXECUTIVE DASHBOARD ────────────────────────────────
function renderDashboardView() {
  const d = state.dashboard || {};
  return `
    <div class="space-y-6">
      <!-- Top 6 Financial Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- 1. Kitna Lena Hai -->
        <div class="glass-card rounded-2xl p-5 border border-rose-500/20 bg-gradient-to-br from-rose-950/20 to-slate-900">
          <div class="flex items-center justify-between text-rose-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Kitna Lena Hai</span>
            <div class="w-8 h-8 rounded-xl bg-rose-500/10 flex items-center justify-center"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-rose-400">${formatINR(d.paymentDue)}</p>
          <p class="text-[11px] text-slate-400 mt-1">${d.activeDueClientsCount || 0} active clients pending</p>
        </div>

        <!-- 2. This Month Collection -->
        <div class="glass-card rounded-2xl p-5 border border-emerald-500/20 bg-gradient-to-br from-emerald-950/20 to-slate-900">
          <div class="flex items-center justify-between text-emerald-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Month Collection</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center"><i data-lucide="trending-up" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-emerald-400">${formatINR(d.monthlyCollection)}</p>
          <p class="text-[11px] text-slate-400 mt-1">Today: <span class="font-bold text-white">${formatINR(d.todayCollection)}</span></p>
        </div>

        <!-- 3. Salary & Commission Me Kitna Dena Hai -->
        <div class="glass-card rounded-2xl p-5 border border-amber-500/20 bg-gradient-to-br from-amber-950/20 to-slate-900">
          <div class="flex items-center justify-between text-amber-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Salary/Comm Due</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center"><i data-lucide="users" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-amber-400">${formatINR(d.totalSalaryPayableThisMonth)}</p>
          <p class="text-[11px] text-slate-400 mt-1">Commission: <span class="font-bold text-white">${formatINR(d.totalCommissionThisMonth)}</span></p>
        </div>

        <!-- 4. Monthly Deductible Expenses -->
        <div class="glass-card rounded-2xl p-5 border border-purple-500/20 bg-gradient-to-br from-purple-950/20 to-slate-900">
          <div class="flex items-center justify-between text-purple-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Monthly Expenses</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 flex items-center justify-center"><i data-lucide="receipt" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-purple-400">${formatINR(d.monthlyExpenses)}</p>
          <p class="text-[11px] text-slate-400 mt-1">Deductible in calculation</p>
        </div>

        <!-- 5. Net Projected Savings -->
        <div class="glass-card rounded-2xl p-5 border border-cyan-500/20 bg-gradient-to-br from-cyan-950/20 to-slate-900">
          <div class="flex items-center justify-between text-cyan-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Projected Bachat</span>
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 flex items-center justify-center"><i data-lucide="piggy-bank" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black ${d.netProjectedBachat >= 0 ? 'text-cyan-400' : 'text-rose-400'}">${formatINR(d.netProjectedBachat)}</p>
          <p class="text-[11px] text-slate-400 mt-1">Lena Hai - Salary - Expense</p>
        </div>

        <!-- 6. Available Cash Fund -->
        <div class="glass-card rounded-2xl p-5 border border-indigo-500/20 bg-gradient-to-br from-indigo-950/20 to-slate-900">
          <div class="flex items-center justify-between text-indigo-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Available Fund</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 flex items-center justify-center"><i data-lucide="landmark" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-indigo-400">${formatINR(d.availableFund)}</p>
          <p class="text-[11px] text-slate-400 mt-1">Total in-hand bank balance</p>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="glass-panel rounded-2xl p-4 flex flex-wrap gap-3 items-center">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2">Quick Actions:</span>
        <button onclick="window.openModal('addClient')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-indigo-600/20">
          <i data-lucide="user-plus" class="w-4 h-4"></i> + Add Client
        </button>
        <button onclick="window.openModal('addEmployee')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all border border-slate-700">
          <i data-lucide="user-check" class="w-4 h-4"></i> + Add Employee
        </button>
        <button onclick="window.openModal('addExpense')" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-amber-600/20">
          <i data-lucide="plus-circle" class="w-4 h-4"></i> + Add Expense
        </button>
        <button onclick="window.openModal('giveAdvance')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-emerald-600/20">
          <i data-lucide="arrow-down-circle" class="w-4 h-4"></i> 💸 Give Advance
        </button>
        <button onclick="window.openModal('cutSalary')" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-rose-600/20">
          <i data-lucide="scissors" class="w-4 h-4"></i> ✂️ Cut Salary
        </button>
      </div>

      <!-- 2 Columns: Upcoming Renewals & Live Activity Logs -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Renewals -->
        <div class="glass-panel rounded-3xl p-6 border border-slate-800">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <i data-lucide="clock" class="w-4 h-4 text-indigo-400"></i> Upcoming Client Renewals
            </h3>
            <button onclick="window.switchTab('clients')" class="text-xs text-indigo-400 hover:underline">View All</button>
          </div>
          <div class="space-y-3">
            ${(d.upcomingRenewals || []).length === 0 ? '<p class="text-xs text-slate-500 py-4 text-center">No upcoming renewals this week.</p>' : ''}
            ${(d.upcomingRenewals || []).map(r => `
              <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800">
                <div>
                  <h4 class="text-sm font-bold text-white">${r.client_name}</h4>
                  <p class="text-xs text-slate-400">Expires: <span class="text-amber-400 font-semibold">${formatDate(r.end_date)}</span> • ${r.client_mobile}</p>
                </div>
                <button onclick="window.openRenewModal(${r.client_id}, '${r.client_name}', ${r.package_amount})" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold">
                  🔄 Renew
                </button>
              </div>
            `).join('')}
          </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="glass-panel rounded-3xl p-6 border border-slate-800">
          <h3 class="text-base font-bold text-white flex items-center gap-2 mb-4">
            <i data-lucide="activity" class="w-4 h-4 text-emerald-400"></i> Recent Audit Activity
          </h3>
          <div class="space-y-3 max-h-72 overflow-y-auto">
            ${(d.activities || []).map(a => `
              <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-900/60 border border-slate-800 text-xs">
                <div class="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-slate-200 font-semibold">${a.description}</p>
                  <p class="text-[10px] text-slate-500 mt-0.5">${a.user_name || 'System'} • ${formatDate(a.created_at)}</p>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>
  `;
}

// ─── VIEW 2: CLIENTS MODULE ─────────────────────────────────────
function renderClientsView() {
  const filtered = state.clients.filter(c => {
    const matchesTab = c.status === state.activeClientSubTab;
    const matchesSearch = !state.clientSearchQuery || 
      c.name.toLowerCase().includes(state.clientSearchQuery.toLowerCase()) || 
      c.mobile.includes(state.clientSearchQuery);
    return matchesTab && matchesSearch;
  });

  return `
    <div class="space-y-6">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex items-center gap-2 p-1 rounded-2xl bg-slate-900 border border-slate-800 w-fit">
          <button onclick="window.setClientSubTab('active')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all ${state.activeClientSubTab === 'active' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'}">
            Active Clients (${state.activeClientsCount})
          </button>
          <button onclick="window.setClientSubTab('inactive')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all ${state.activeClientSubTab === 'inactive' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-400 hover:text-white'}">
            Inactive Clients (${state.inactiveClientsCount})
          </button>
        </div>

        <!-- Search & Add -->
        <div class="flex items-center gap-3">
          <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
            <input type="text" oninput="window.setClientSearch(this.value)" value="${state.clientSearchQuery}" placeholder="Search client..." class="pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500 w-48 sm:w-64">
          </div>
          <button onclick="window.openModal('addClient')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/20">
            <i data-lucide="plus" class="w-4 h-4"></i> + Add Client
          </button>
        </div>
      </div>

      <!-- Clients List -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        ${filtered.length === 0 ? '<div class="col-span-full text-center py-12 text-slate-500 text-sm">No clients found.</div>' : ''}
        ${filtered.map(c => `
          <div class="glass-panel rounded-3xl p-5 border border-slate-800 hover:border-indigo-500/40 transition-all flex flex-col justify-between">
            <div>
              <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                  <h3 class="text-base font-bold text-white">${c.name}</h3>
                  <p class="text-xs text-slate-400 mt-0.5">📱 ${c.mobile} ${c.mobile_secondary ? `/ ${c.mobile_secondary}` : ''}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${c.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">
                  ${c.status.toUpperCase()}
                </span>
              </div>

              <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-1.5 text-xs text-slate-300 mb-4">
                <div class="flex justify-between">
                  <span class="text-slate-500">Package:</span>
                  <span class="font-bold text-white">${formatINR(c.current_package)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Pending Due:</span>
                  <span class="font-bold ${parseFloat(c.total_due) > 0 ? 'text-rose-400' : 'text-emerald-400'}">${formatINR(c.total_due)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Location:</span>
                  <span>${c.work_location || '—'}</span>
                </div>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center gap-2 pt-2 border-t border-slate-800/60">
              <button onclick="window.openRenewModal(${c.id}, '${c.name}', ${c.current_package})" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1">
                🔄 Renew
              </button>
              <button onclick="window.openPaymentModal(${c.id}, '${c.name}', ${c.total_due > 0 ? c.total_due : c.current_package})" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1">
                💰 Pay
              </button>
              ${c.status === 'inactive' ? `
                <button onclick="window.openDeleteClientModal(${c.id}, '${c.name}')" title="Delete Inactive Client" class="p-2 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              ` : `
                <button onclick="window.toggleClientStatus(${c.id}, 'inactive')" title="Mark Inactive" class="p-2 bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                  <i data-lucide="pause-circle" class="w-4 h-4"></i>
                </button>
              `}
            </div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

// ─── VIEW 3: EMPLOYEES MODULE ───────────────────────────────────
function renderEmployeesView() {
  return `
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-bold text-white">Active Employees (${state.employees.length})</h2>
        <button onclick="window.openModal('addEmployee')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/20">
          <i data-lucide="user-plus" class="w-4 h-4"></i> + Add Employee
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        ${state.employees.map(e => `
          <div class="glass-panel rounded-3xl p-5 border border-slate-800">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 text-indigo-400 font-black text-sm flex items-center justify-center border border-indigo-500/30">
                  ${e.name.substring(0, 2).toUpperCase()}
                </div>
                <div>
                  <h3 class="text-sm font-bold text-white">${e.name}</h3>
                  <p class="text-xs text-slate-400">${e.role_title} • ${e.phone}</p>
                </div>
              </div>
            </div>

            <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1.5 text-xs text-slate-300 mb-4">
              <div class="flex justify-between">
                <span class="text-slate-500">Salary Type:</span>
                <span class="font-bold text-indigo-300 capitalize">${e.salary_type}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Fixed Salary:</span>
                <span class="font-bold text-white">${formatINR(e.fixed_salary)}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Pending Advance:</span>
                <span class="font-bold text-rose-400">${formatINR(e.pending_advance)}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Active Clients:</span>
                <span class="font-bold text-emerald-400">${e.active_clients_count || 0}</span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button onclick="window.openCutSalaryModal(${e.id}, '${e.name}')" class="flex-1 py-2 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1 border border-rose-500/30">
                <i data-lucide="scissors" class="w-3.5 h-3.5"></i> Cut Salary
              </button>
              <button onclick="window.openGiveAdvanceModal(${e.id}, '${e.name}')" class="flex-1 py-2 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1 border border-emerald-500/30">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Advance
              </button>
            </div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

// ─── VIEW 4: SALARIES & PAYOUTS ─────────────────────────────────
function renderSalariesView() {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return `
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <select onchange="window.setSalaryMonth(this.value)" class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
            ${months.map((m, idx) => `<option value="${idx + 1}" ${state.salaryMonth === idx + 1 ? 'selected' : ''}>${m}</option>`).join('')}
          </select>
          <select onchange="window.setSalaryYear(this.value)" class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
            <option value="2026" selected>2026</option>
            <option value="2027">2027</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button onclick="window.openModal('giveAdvance')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
            <i data-lucide="plus" class="w-4 h-4"></i> Give Advance
          </button>
        </div>
      </div>

      <!-- Salary Payout Table -->
      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-800">
              <tr>
                <th class="p-4">Employee</th>
                <th class="p-4">Base Salary</th>
                <th class="p-4">Commission</th>
                <th class="p-4">Advance Deducted</th>
                <th class="p-4">Cut / Deductions</th>
                <th class="p-4">Net Payable</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              ${state.salaries.map(s => `
                <tr class="hover:bg-slate-900/40 transition-all">
                  <td class="p-4">
                    <p class="font-bold text-white">${s.employee?.name || '—'}</p>
                    <p class="text-[11px] text-slate-500">${s.employee?.role_title || ''}</p>
                  </td>
                  <td class="p-4 font-semibold text-slate-300">${formatINR(s.base_salary)}</td>
                  <td class="p-4 font-semibold text-indigo-400">${formatINR(s.total_commission)}</td>
                  <td class="p-4 font-semibold text-amber-400">-${formatINR(s.advance_deducted)}</td>
                  <td class="p-4 font-semibold text-rose-400">-${formatINR(s.other_deductions)}</td>
                  <td class="p-4 font-bold text-emerald-400 text-sm">${formatINR(s.net_payable)}</td>
                  <td class="p-4">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${s.status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">
                      ${s.status ? s.status.toUpperCase() : 'PENDING'}
                    </span>
                  </td>
                  <td class="p-4 text-right space-x-1.5">
                    ${s.status === 'paid' ? `
                      <span class="text-xs text-slate-500 font-semibold">✅ Paid</span>
                    ` : `
                      <button onclick="window.markSalaryPaid(${s.employee_id}, ${s.net_payable})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm">
                        💰 Mark Paid
                      </button>
                    `}
                    <button onclick="window.openCutSalaryModal(${s.employee_id}, '${s.employee?.name}')" class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-xl text-xs font-bold">
                      ✂️ Cut
                    </button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `;
}

// ─── VIEW 5: UNIFIED EXPENSES ───────────────────────────────────
function renderExpensesView() {
  return `
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Summary Pills -->
        <div class="flex flex-wrap items-center gap-3">
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">General Expenses:</span>
            <span class="font-bold text-white ml-1.5">${formatINR(state.expenseTotals.totalGeneral)}</span>
          </div>
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">Paid Salaries:</span>
            <span class="font-bold text-indigo-400 ml-1.5">${formatINR(state.expenseTotals.totalSalaryPaid)}</span>
          </div>
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">Deductible in Bachat:</span>
            <span class="font-bold text-purple-400 ml-1.5">${formatINR(state.expenseTotals.totalDeductible)}</span>
          </div>
        </div>

        <button onclick="window.openModal('addExpense')" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="plus" class="w-4 h-4"></i> + Add Expense
        </button>
      </div>

      <!-- Expenses Table -->
      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-800">
              <tr>
                <th class="p-4">Include in Bachat?</th>
                <th class="p-4">Date</th>
                <th class="p-4">Title / Category</th>
                <th class="p-4">Amount</th>
                <th class="p-4">Notes</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              ${state.expenses.map(e => `
                <tr class="hover:bg-slate-900/40 transition-all ${e.include_in_calculation ? '' : 'opacity-50'}">
                  <td class="p-4">
                    ${e.entry_type === 'general' ? `
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" onchange="window.toggleExpenseCalculation(${e.id}, this.checked)" ${e.include_in_calculation ? 'checked' : ''} class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600">
                        <span class="text-[11px] text-slate-400">${e.include_in_calculation ? 'Included' : 'Excluded'}</span>
                      </label>
                    ` : '<span class="text-[11px] text-indigo-400 font-semibold">Auto-Counted</span>'}
                  </td>
                  <td class="p-4 text-slate-400">${formatDate(e.expense_date)}</td>
                  <td class="p-4">
                    <p class="font-bold text-white">${e.title}</p>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 border border-slate-700">${e.category_name || 'General'}</span>
                  </td>
                  <td class="p-4 font-black text-rose-400 text-sm">${formatINR(e.amount)}</td>
                  <td class="p-4 text-slate-400">${e.notes || '—'}</td>
                  <td class="p-4 text-right">
                    ${e.entry_type === 'general' ? `
                      <button onclick="window.deleteExpense(${e.id})" class="p-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-lg transition-all">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                      </button>
                    ` : '—'}
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `;
}

// ─── VIEW 6: WORK TRACKER ───────────────────────────────────────
function renderWorkTrackerView() {
  return `
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-bold text-white">Assigned Work Tracker</h2>
      </div>

      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-800">
              <tr>
                <th class="p-4">Client</th>
                <th class="p-4">Assigned Employee</th>
                <th class="p-4">Assigned From Date</th>
                <th class="p-4">Location</th>
                <th class="p-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              ${state.workTracker.map(w => `
                <tr class="hover:bg-slate-900/40 transition-all">
                  <td class="p-4">
                    <p class="font-bold text-white">${w.client_name}</p>
                    <p class="text-[11px] text-slate-500">${w.client_mobile}</p>
                  </td>
                  <td class="p-4 font-semibold text-indigo-300">${w.employee_name}</td>
                  <td class="p-4 text-amber-400 font-semibold">${formatDate(w.assigned_date)}</td>
                  <td class="p-4 text-slate-400">${w.work_location || '—'}</td>
                  <td class="p-4 text-right">
                    <button onclick="window.openWorkHistoryModal(${w.client_id}, '${w.client_name}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold">
                      📅 Month History
                    </button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `;
}

// ─── MODAL MANAGER ──────────────────────────────────────────────
function renderModal() {
  if (!state.activeModal) return '';

  return `
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm overflow-y-auto">
      <div class="glass-panel bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-700 shadow-2xl relative">
        <button onclick="window.closeModal()" class="absolute top-6 right-6 text-slate-400 hover:text-white font-bold text-xl">&times;</button>
        
        ${renderModalContent()}
      </div>
    </div>
  `;
}

function renderModalContent() {
  const m = state.activeModal;
  const p = state.modalPayload || {};

  if (m === 'addClient') {
    return `
      <h3 class="text-lg font-bold text-white mb-4">Add New Client</h3>
      <form id="addClientForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Client Name *</label>
          <input type="text" name="name" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Primary Mobile *</label>
            <input type="text" name="mobile" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Secondary Mobile</label>
            <input type="text" name="mobile_secondary" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Monthly Package (₹) *</label>
            <input type="number" name="current_package" required value="5000" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">GST Count</label>
            <input type="number" name="gst_count" value="1" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Service Start Date *</label>
            <input type="date" name="service_start_date" value="${new Date().toISOString().split('T')[0]}" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Work Location</label>
            <input type="text" name="work_location" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg mt-4">Save Client</button>
      </form>
    `;
  }

  if (m === 'renewClient') {
    const nextStart = new Date().toISOString().split('T')[0];
    const nextEnd = new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0];
    return `
      <h3 class="text-lg font-bold text-white mb-2">🔄 Renew Client — ${p.clientName}</h3>
      <form id="renewClientForm" class="space-y-4 text-xs">
        <div class="p-3 bg-indigo-950/40 rounded-xl border border-indigo-800/40">
          <label class="block font-semibold text-slate-300 mb-2">Package Renewal Option:</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-2 p-2 bg-slate-900 rounded-lg cursor-pointer">
              <input type="radio" name="package_option" value="same" checked onchange="document.getElementById('customPkgBox').style.display='none'">
              <span>Same (₹${p.packageAmount})</span>
            </label>
            <label class="flex items-center gap-2 p-2 bg-slate-900 rounded-lg cursor-pointer">
              <input type="radio" name="package_option" value="new" onchange="document.getElementById('customPkgBox').style.display='block'">
              <span>New Package</span>
            </label>
          </div>
          <div id="customPkgBox" class="mt-3 hidden">
            <label class="block font-semibold text-slate-300 mb-1">New Package Amount (₹)</label>
            <input type="number" name="custom_package_amount" value="${p.packageAmount}" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Start Date</label>
            <input type="date" name="start_date" value="${nextStart}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">End Date</label>
            <input type="date" name="end_date" value="${nextEnd}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
          </div>
        </div>

        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
          <label class="flex items-center gap-2 font-bold text-emerald-400 cursor-pointer">
            <input type="checkbox" name="collect_payment" onchange="document.getElementById('collectPaymentBox').style.display = this.checked ? 'block' : 'none'">
            <span>💰 Collect Payment Now?</span>
          </label>
          <div id="collectPaymentBox" class="mt-3 space-y-3 hidden">
            <div>
              <label class="block text-slate-400 mb-1">Payment Amount (₹)</label>
              <input type="number" name="payment_amount" value="${p.packageAmount}" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white">
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Payment Method</label>
              <select name="payment_method" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white">
                <option value="cash">Cash</option>
                <option value="upi">UPI</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
          </div>
        </div>

        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg mt-4">Confirm Renewal</button>
      </form>
    `;
  }

  if (m === 'receivePayment') {
    return `
      <h3 class="text-lg font-bold text-white mb-2">💰 Receive Payment — ${p.clientName}</h3>
      <form id="receivePaymentForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Amount (₹) *</label>
          <input type="number" name="amount" required value="${p.amount}" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Payment Date *</label>
            <input type="date" name="payment_date" value="${new Date().toISOString().split('T')[0]}" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Payment Method *</label>
            <select name="payment_method" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
              <option value="cash">Cash</option>
              <option value="upi">UPI</option>
              <option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Notes</label>
          <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white" placeholder="Optional notes"></textarea>
        </div>
        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg mt-4">Save Payment</button>
      </form>
    `;
  }

  if (m === 'cutSalary') {
    return `
      <h3 class="text-lg font-bold text-white mb-2">✂️ Cut Employee Salary</h3>
      <p class="text-xs text-slate-400 mb-4">Employee: <strong class="text-white">${p.employeeName}</strong></p>
      <form id="cutSalaryForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Cut Amount (₹) *</label>
          <input type="number" name="amount" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold">
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Reason for Deduction *</label>
          <textarea name="reason" required rows="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white" placeholder="e.g. Penalty for 2 absent days or work delay"></textarea>
        </div>
        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg mt-4">Apply Deduction</button>
      </form>
    `;
  }

  if (m === 'giveAdvance') {
    return `
      <h3 class="text-lg font-bold text-white mb-4">💸 Disburse Salary Advance</h3>
      <form id="giveAdvanceForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Select Employee *</label>
          <select name="employee_id" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
            ${state.employees.map(e => `<option value="${e.id}" ${p.employeeId === e.id ? 'selected' : ''}>${e.name}</option>`).join('')}
          </select>
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Advance Amount (₹) *</label>
          <input type="number" name="amount" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold">
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Reason</label>
          <input type="text" name="reason" placeholder="Personal emergency advance" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
        </div>
        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg mt-4">Disburse Advance</button>
      </form>
    `;
  }

  if (m === 'addExpense') {
    return `
      <h3 class="text-lg font-bold text-white mb-4">Add General Expense</h3>
      <form id="addExpenseForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Title *</label>
          <input type="text" name="title" required placeholder="e.g. Office Electricity Bill" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Amount (₹) *</label>
            <input type="number" name="amount" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Expense Date *</label>
            <input type="date" name="expense_date" value="${new Date().toISOString().split('T')[0]}" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
          </div>
        </div>
        <div>
          <label class="flex items-center gap-2 cursor-pointer mt-2">
            <input type="checkbox" name="include_in_calculation" checked class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600">
            <span class="text-slate-300 font-semibold">Include in Monthly Bachat calculation</span>
          </label>
        </div>
        <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl shadow-lg mt-4">Save Expense</button>
      </form>
    `;
  }

  if (m === 'deleteClient') {
    return `
      <h3 class="text-lg font-bold text-rose-400 mb-2 flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-5 h-5"></i> Delete Inactive Client
      </h3>
      <p class="text-xs text-slate-300 mb-4">
        Are you sure you want to permanently delete inactive client <strong class="text-white">${p.clientName}</strong>?
      </p>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="window.closeModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">Cancel</button>
        <button onclick="window.confirmDeleteClient(${p.clientId})" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold">Yes, Delete</button>
      </div>
    `;
  }

  return '';
}

function renderToast() {
  if (!state.toast) return '';
  const isError = state.toast.type === 'error';
  return `
    <div class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-2xl text-xs font-bold ${isError ? 'bg-rose-600 text-white shadow-rose-600/30' : 'bg-emerald-600 text-white shadow-emerald-600/30'} animate-bounce">
      <i data-lucide="${isError ? 'alert-circle' : 'check-circle'}" class="w-4 h-4"></i>
      <span>${state.toast.message}</span>
    </div>
  `;
}

// ─── EVENT HANDLERS & GLOBAL WINDOW ATTACHMENTS ─────────────────
function attachLoginEvents() {
  const form = document.getElementById('loginForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const u = document.getElementById('loginUsername').value;
      const p = document.getElementById('loginPassword').value;
      login(u, p);
    });
  }
}

function attachEventListeners() {
  // Add Client Form
  const addClientForm = document.getElementById('addClientForm');
  if (addClientForm) {
    addClientForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(addClientForm);
      try {
        await api('/api/clients', {
          method: 'POST',
          body: JSON.stringify(Object.fromEntries(fd)),
        });
        showToast('Client added successfully!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }

  // Renew Client Form
  const renewForm = document.getElementById('renewClientForm');
  if (renewForm) {
    renewForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(renewForm);
      const payload = Object.fromEntries(fd);
      payload.collect_payment = fd.get('collect_payment') === 'on';
      try {
        await api(`/api/clients/${state.modalPayload.clientId}/renew`, {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        showToast('Client renewed successfully!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }

  // Receive Payment Form
  const payForm = document.getElementById('receivePaymentForm');
  if (payForm) {
    payForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(payForm);
      try {
        await api(`/api/clients/${state.modalPayload.clientId}/payments`, {
          method: 'POST',
          body: JSON.stringify(Object.fromEntries(fd)),
        });
        showToast('Payment recorded successfully!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }

  // Cut Salary Form
  const cutForm = document.getElementById('cutSalaryForm');
  if (cutForm) {
    cutForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(cutForm);
      try {
        await api(`/api/employees/${state.modalPayload.employeeId}/deductions`, {
          method: 'POST',
          body: JSON.stringify(Object.fromEntries(fd)),
        });
        showToast('Salary cut applied & employee notified!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }

  // Give Advance Form
  const advForm = document.getElementById('giveAdvanceForm');
  if (advForm) {
    advForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(advForm);
      try {
        await api('/api/salary/advance', {
          method: 'POST',
          body: JSON.stringify(Object.fromEntries(fd)),
        });
        showToast('Advance disbursed successfully!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }

  // Add Expense Form
  const expForm = document.getElementById('addExpenseForm');
  if (expForm) {
    expForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(expForm);
      const payload = Object.fromEntries(fd);
      payload.include_in_calculation = fd.get('include_in_calculation') === 'on';
      try {
        await api('/api/expenses', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        showToast('Expense recorded successfully!');
        closeModal();
        loadInitialData();
      } catch {}
    });
  }
}

// Window Global helper bindings for inline onclicks
window.switchTab = switchTab;
window.logout = logout;
window.openModal = (modalName, payload = {}) => {
  state.activeModal = modalName;
  state.modalPayload = payload;
  render();
};
window.closeModal = () => {
  state.activeModal = null;
  state.modalPayload = {};
  render();
};
window.setClientSubTab = (subTab) => {
  state.activeClientSubTab = subTab;
  render();
};
window.setClientSearch = (q) => {
  state.clientSearchQuery = q;
  render();
};
window.openRenewModal = (clientId, clientName, packageAmount) => {
  window.openModal('renewClient', { clientId, clientName, packageAmount });
};
window.openPaymentModal = (clientId, clientName, amount) => {
  window.openModal('receivePayment', { clientId, clientName, amount });
};
window.openCutSalaryModal = (employeeId, employeeName) => {
  window.openModal('cutSalary', { employeeId, employeeName });
};
window.openGiveAdvanceModal = (employeeId, employeeName) => {
  window.openModal('giveAdvance', { employeeId, employeeName });
};
window.openDeleteClientModal = (clientId, clientName) => {
  window.openModal('deleteClient', { clientId, clientName });
};
window.confirmDeleteClient = async (clientId) => {
  try {
    await api(`/api/clients/${clientId}`, { method: 'DELETE' });
    showToast('Inactive client deleted successfully!');
    closeModal();
    loadInitialData();
  } catch {}
};
window.toggleClientStatus = async (clientId, status) => {
  try {
    await api(`/api/clients/${clientId}/status`, {
      method: 'PUT',
      body: JSON.stringify({ status }),
    });
    showToast(`Client marked as ${status}!`);
    loadInitialData();
  } catch {}
};
window.setSalaryMonth = (m) => {
  state.salaryMonth = parseInt(m);
  loadInitialData();
};
window.setSalaryYear = (y) => {
  state.salaryYear = parseInt(y);
  loadInitialData();
};
window.markSalaryPaid = async (employeeId, amount) => {
  try {
    await api('/api/salary/pay', {
      method: 'POST',
      body: JSON.stringify({
        employee_id: employeeId,
        month: state.salaryMonth,
        year: state.salaryYear,
        amount,
      }),
    });
    showToast('Salary marked as paid!');
    loadInitialData();
  } catch {}
};
window.toggleExpenseCalculation = async (expenseId, includeInCalculation) => {
  try {
    await api(`/api/expenses/${expenseId}/toggle-calculation`, {
      method: 'PATCH',
      body: JSON.stringify({ include_in_calculation: includeInCalculation }),
    });
    showToast('Expense calculation flag updated!');
    loadInitialData();
  } catch {}
};
window.deleteExpense = async (expenseId) => {
  try {
    await api(`/api/expenses/${expenseId}`, { method: 'DELETE' });
    showToast('Expense deleted!');
    loadInitialData();
  } catch {}
};

// Initial App Boot
if (state.token) {
  loadInitialData();
} else {
  render();
}
