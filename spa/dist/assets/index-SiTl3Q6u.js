(function(){let e=document.createElement(`link`).relList;if(e&&e.supports&&e.supports(`modulepreload`))return;for(let e of document.querySelectorAll(`link[rel="modulepreload"]`))n(e);new MutationObserver(e=>{for(let t of e)if(t.type===`childList`)for(let e of t.addedNodes)e.tagName===`LINK`&&e.rel===`modulepreload`&&n(e)}).observe(document,{childList:!0,subtree:!0});function t(e){let t={};return e.integrity&&(t.integrity=e.integrity),e.referrerPolicy&&(t.referrerPolicy=e.referrerPolicy),t.credentials=e.crossOrigin===`use-credentials`?`include`:e.crossOrigin===`anonymous`?`omit`:`same-origin`,t}function n(e){if(e.ep)return;e.ep=!0;let n=t(e);fetch(e.href,n)}})(),((e,t)=>()=>(t||(e((t={exports:{}}).exports,t),e=null),t.exports))((()=>{var e={token:localStorage.getItem(`erp_token`)||null,user:JSON.parse(localStorage.getItem(`erp_user`)||`null`),currentTab:`dashboard`,activeClientSubTab:`active`,dashboard:null,clients:[],activeClientsCount:0,inactiveClientsCount:0,clientSearchQuery:``,managers:[],payments:[],employees:[],salaries:[],salaryMonth:new Date().getMonth()+1,salaryYear:new Date().getFullYear(),expenses:[],expenseMonth:new Date().getMonth()+1,expenseYear:new Date().getFullYear(),expenseCategories:[],expenseTotals:{totalGeneral:0,totalSalaryPaid:0,totalDeductible:0},workTracker:[],investors:[],investments:[],reports:null,systemUsers:[],activeModal:null,modalPayload:{},toast:null,loading:!1};async function t(t,r={}){let a={"Content-Type":`application/json`,...r.headers||{}};e.token&&(a.Authorization=`Bearer ${e.token}`);try{let e=await fetch(t,{...r,headers:a}),n=await e.json();if(!e.ok)throw e.status===401&&i(),Error(n.error||`Request failed`);return n}catch(e){throw n(e.message,`error`),e}}function n(t,n=`success`){e.toast={message:t,type:n},l(),setTimeout(()=>{e.toast=null,l()},4e3)}async function r(r,i){e.loading=!0,l();try{let o=await t(`/api/auth/login`,{method:`POST`,body:JSON.stringify({login:r,password:i})});e.token=o.token,e.user=o.user,localStorage.setItem(`erp_token`,o.token),localStorage.setItem(`erp_user`,JSON.stringify(o.user)),e.currentTab=`dashboard`,n(`Welcome back, ${o.user.name}!`),await a()}catch{}finally{e.loading=!1,l()}}function i(){e.token=null,e.user=null,localStorage.removeItem(`erp_token`),localStorage.removeItem(`erp_user`),l()}async function a(){if(e.token){e.loading=!0,l();try{if(e.currentTab===`dashboard`)e.dashboard=await t(`/api/dashboard`);else if(e.currentTab===`clients`){let n=await t(`/api/clients`);e.clients=n.clients,e.activeClientsCount=n.activeCount,e.inactiveClientsCount=n.inactiveCount,e.managers=n.managers||[],e.employees=n.employees||[]}else if(e.currentTab===`payments`)e.payments=(await t(`/api/payments`)).payments;else if(e.currentTab===`employees`)e.employees=(await t(`/api/employees`)).employees;else if(e.currentTab===`salary`)e.salaries=(await t(`/api/salary?month=${e.salaryMonth}&year=${e.salaryYear}`)).salaries;else if(e.currentTab===`expenses`){let n=await t(`/api/expenses?month=${e.expenseMonth}&year=${e.expenseYear}`);e.expenses=n.expenses,e.expenseCategories=n.categories,e.expenseTotals={totalGeneral:n.totalGeneral,totalSalaryPaid:n.totalSalaryPaid,totalDeductible:n.totalDeductible}}else if(e.currentTab===`work-tracker`){let n=await t(`/api/work-tracker`);e.workTracker=n.assignments,e.employees=n.employees}else if(e.currentTab===`investors`||e.currentTab===`investments`){let n=await t(`/api/investors`);e.investors=n.investors,e.investments=n.investments}else e.currentTab===`reports`?e.reports=await t(`/api/reports`):e.currentTab===`users`&&(e.systemUsers=(await t(`/api/settings/users`)).users)}catch(e){console.error(e)}finally{e.loading=!1,l()}}}function o(t){e.currentTab=t,e.activeModal=null,a()}function s(e){return`₹`+parseFloat(e||0).toLocaleString(`en-IN`,{minimumFractionDigits:2,maximumFractionDigits:2})}function c(e){if(!e)return`—`;try{return new Date(e).toLocaleDateString(`en-IN`,{day:`2-digit`,month:`2-digit`,year:`numeric`})}catch{return e}}function l(){let t=document.getElementById(`app`);if(!e.token){t.innerHTML=u(),lucide.createIcons(),D();return}t.innerHTML=`
    <div class="h-full flex flex-col md:flex-row overflow-hidden bg-slate-950">
      ${d()}
      
      <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-900/40">
        ${f()}
        
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 space-y-6">
          ${p()}
        </div>
      </main>

      ${w()}
      ${E()}
    </div>
  `,lucide.createIcons(),O()}function u(){return`
    <div class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-indigo-950 via-slate-950 to-slate-900 relative overflow-hidden">
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
              <input type="checkbox" checked class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600">
              <span>Remember me</span>
            </label>
            <span class="text-indigo-400 hover:text-indigo-300 cursor-pointer">Quick Access</span>
          </div>

          <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
            ${e.loading?`<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Signing in...`:`<i data-lucide="log-in" class="w-4 h-4"></i> Sign In to Dashboard`}
          </button>
        </form>

        <p class="text-center text-slate-500 text-xs mt-6">© ${new Date().getFullYear()} Listing ERP • Ultra Fast Netlify SPA</p>
      </div>
    </div>
  `}function d(){return`
    <aside class="w-full md:w-64 lg:w-72 bg-slate-950 border-r border-slate-800/80 flex flex-col flex-shrink-0">
      <div class="p-6 flex items-center justify-between border-b border-slate-800/60">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-base shadow-md shadow-blue-600/30">
            L
          </div>
          <div>
            <h2 class="font-bold text-white tracking-tight leading-none text-sm">Listing ERP</h2>
            <span class="text-[10px] font-medium text-emerald-400 flex items-center gap-1 mt-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Supabase Connected
            </span>
          </div>
        </div>
      </div>

      <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto">
        ${[{title:`Main Navigation`,items:[{id:`dashboard`,label:`Dashboard`,icon:`layout-dashboard`},{id:`clients`,label:`Clients Management`,icon:`users`,badge:e.activeClientsCount||null},{id:`payments`,label:`Payments Ledger`,icon:`credit-card`}]},{title:`Human Resources`,items:[{id:`employees`,label:`All Employees`,icon:`user-check`},{id:`salary`,label:`Salary & Payroll`,icon:`wallet`},{id:`work-tracker`,label:`Work Tracker`,icon:`calendar-check`}]},{title:`Finance & Accounting`,items:[{id:`expenses`,label:`Expenses`,icon:`receipt`},{id:`investors`,label:`Investors Group`,icon:`trending-up`},{id:`reports`,label:`Financial Reports`,icon:`bar-chart-3`}]},{title:`Administration`,items:[{id:`users`,label:`System Users`,icon:`shield-check`}]}].map(t=>`
          <div>
            <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">${t.title}</p>
            <div class="space-y-1">
              ${t.items.map(t=>`
                <button onclick="window.switchTab('${t.id}')" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all ${e.currentTab===t.id?`bg-blue-600 text-white shadow-md shadow-blue-600/25`:`text-slate-400 hover:text-slate-100 hover:bg-slate-900`}">
                  <div class="flex items-center gap-3">
                    <i data-lucide="${t.icon}" class="w-4 h-4"></i>
                    <span>${t.label}</span>
                  </div>
                  ${t.badge?`<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-950 text-blue-300 border border-blue-700/50">${t.badge}</span>`:``}
                </button>
              `).join(``)}
            </div>
          </div>
        `).join(``)}
      </nav>

      <div class="p-4 border-t border-slate-800/60">
        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 font-bold text-xs flex items-center justify-center border border-blue-500/30 flex-shrink-0">
              ${(e.user?.name||`A`).charAt(0)}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold text-white truncate">${e.user?.name||`Admin`}</p>
              <p class="text-[10px] text-slate-400 truncate">${e.user?.role||`Main Admin`}</p>
            </div>
          </div>
          <button onclick="window.logout()" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-all flex-shrink-0">
            <i data-lucide="log-out" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
    </aside>
  `}function f(){return`
    <header class="h-16 bg-slate-950/60 backdrop-blur-md border-b border-slate-800/60 px-6 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-3">
        <h1 class="text-base font-bold text-white">
          ${{dashboard:`Executive Dashboard`,clients:`Clients Hub`,payments:`Payments Ledger`,employees:`Employees Directory`,salary:`Salary & Payroll Hub`,"work-tracker":`Work Tracker & History`,expenses:`Expenses Management`,investors:`Investors & Investments`,reports:`Financial Reports`,users:`System Users & Roles`}[e.currentTab]||e.currentTab}
        </h1>
      </div>

      <div class="flex items-center gap-2">
        <button onclick="window.openModal('addClient')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-md transition-all">
          <i data-lucide="plus" class="w-3.5 h-3.5"></i> + Add Client
        </button>
        <button onclick="window.openModal('addExpense')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-md transition-all">
          <i data-lucide="plus" class="w-3.5 h-3.5"></i> + Add Expense
        </button>
      </div>
    </header>
  `}function p(){return e.loading&&!e.dashboard&&!e.clients.length?`
      <div class="h-64 flex items-center justify-center text-slate-400">
        <div class="flex flex-col items-center gap-3">
          <i data-lucide="loader" class="w-8 h-8 animate-spin text-blue-500"></i>
          <p class="text-xs font-semibold">Loading data from Supabase...</p>
        </div>
      </div>
    `:e.currentTab===`dashboard`?m():e.currentTab===`clients`?h():e.currentTab===`payments`?g():e.currentTab===`employees`?_():e.currentTab===`salary`?v():e.currentTab===`work-tracker`?b():e.currentTab===`expenses`?y():e.currentTab===`investors`?x():e.currentTab===`reports`?S():e.currentTab===`users`?C():`<div>Tab not found</div>`}function m(){let t=e.dashboard||{};return`
    <div class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-rose-500/20 bg-gradient-to-br from-rose-950/20 to-slate-900">
          <div class="flex items-center justify-between text-rose-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Kitna Lena Hai</span>
            <div class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-rose-400">${s(t.paymentDue)}</p>
          <p class="text-[10px] text-slate-400 mt-1">${t.activeDueClientsCount||0} active clients pending</p>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-emerald-500/20 bg-gradient-to-br from-emerald-950/20 to-slate-900">
          <div class="flex items-center justify-between text-emerald-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Month Collection</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center"><i data-lucide="trending-up" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-emerald-400">${s(t.monthlyCollection)}</p>
          <p class="text-[10px] text-slate-400 mt-1">Today: <span class="font-bold text-white">${s(t.todayCollection)}</span></p>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-amber-500/20 bg-gradient-to-br from-amber-950/20 to-slate-900">
          <div class="flex items-center justify-between text-amber-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Salary/Comm Due</span>
            <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center"><i data-lucide="users" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-amber-400">${s(t.totalSalaryPayableThisMonth)}</p>
          <p class="text-[10px] text-slate-400 mt-1">Commission: <span class="font-bold text-white">${s(t.totalCommissionThisMonth)}</span></p>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-purple-500/20 bg-gradient-to-br from-purple-950/20 to-slate-900">
          <div class="flex items-center justify-between text-purple-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Monthly Expenses</span>
            <div class="w-7 h-7 rounded-lg bg-purple-500/10 flex items-center justify-center"><i data-lucide="receipt" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-purple-400">${s(t.monthlyExpenses)}</p>
          <p class="text-[10px] text-slate-400 mt-1">Deductible in calculation</p>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-cyan-500/20 bg-gradient-to-br from-cyan-950/20 to-slate-900">
          <div class="flex items-center justify-between text-cyan-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Projected Bachat</span>
            <div class="w-7 h-7 rounded-lg bg-cyan-500/10 flex items-center justify-center"><i data-lucide="piggy-bank" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black ${t.netProjectedBachat>=0?`text-cyan-400`:`text-rose-400`}">${s(t.netProjectedBachat)}</p>
          <p class="text-[10px] text-slate-400 mt-1">Lena - Salary - Expense</p>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-blue-500/20 bg-gradient-to-br from-blue-950/20 to-slate-900">
          <div class="flex items-center justify-between text-blue-400 mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider">Available Fund</span>
            <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center"><i data-lucide="landmark" class="w-4 h-4"></i></div>
          </div>
          <p class="text-2xl font-black text-blue-400">${s(t.availableFund)}</p>
          <p class="text-[10px] text-slate-400 mt-1">Actual bank fund</p>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="glass-panel rounded-2xl p-4 flex flex-wrap gap-2.5 items-center">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2">Quick Actions:</span>
        <button onclick="window.openModal('addClient')" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> + Add Client
        </button>
        <button onclick="window.openModal('addEmployee')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 border border-slate-700">
          <i data-lucide="user-check" class="w-3.5 h-3.5"></i> + Add Employee
        </button>
        <button onclick="window.openModal('addExpense')" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> + Add Expense
        </button>
        <button onclick="window.openModal('giveAdvance')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="arrow-down-circle" class="w-3.5 h-3.5"></i> 💸 Give Advance
        </button>
        <button onclick="window.openModal('cutSalary')" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="scissors" class="w-3.5 h-3.5"></i> ✂️ Cut Salary
        </button>
      </div>

      <!-- 2 Columns: Upcoming Renewals & Live Activity Logs -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-panel rounded-3xl p-6 border border-slate-800">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <i data-lucide="clock" class="w-4 h-4 text-blue-400"></i> Upcoming Client Renewals
            </h3>
            <button onclick="window.switchTab('clients')" class="text-xs text-blue-400 hover:underline">View All</button>
          </div>
          <div class="space-y-2.5">
            ${(t.upcomingRenewals||[]).length===0?`<p class="text-xs text-slate-500 py-4 text-center">No upcoming renewals this week.</p>`:``}
            ${(t.upcomingRenewals||[]).map(e=>`
              <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900/80 border border-slate-800">
                <div>
                  <h4 class="text-xs font-bold text-white">${e.client_name}</h4>
                  <p class="text-[11px] text-slate-400">Expires: <span class="text-amber-400 font-semibold">${c(e.end_date)}</span> • ${e.client_mobile}</p>
                </div>
                <button onclick="window.openRenewModal(${e.client_id}, '${e.client_name}', ${e.package_amount})" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold">
                  🔄 Renew
                </button>
              </div>
            `).join(``)}
          </div>
        </div>

        <div class="glass-panel rounded-3xl p-6 border border-slate-800">
          <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-4">
            <i data-lucide="activity" class="w-4 h-4 text-emerald-400"></i> Recent Audit Activity
          </h3>
          <div class="space-y-2.5 max-h-72 overflow-y-auto">
            ${(t.activities||[]).map(e=>`
              <div class="flex items-start gap-2.5 p-2.5 rounded-2xl bg-slate-900/60 border border-slate-800 text-xs">
                <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-slate-200 font-semibold">${e.description}</p>
                  <p class="text-[10px] text-slate-500 mt-0.5">${e.user_name||`System`} • ${c(e.created_at)}</p>
                </div>
              </div>
            `).join(``)}
          </div>
        </div>
      </div>
    </div>
  `}function h(){let t=e.clients.filter(t=>{let n=t.status===e.activeClientSubTab,r=!e.clientSearchQuery||t.name.toLowerCase().includes(e.clientSearchQuery.toLowerCase())||t.mobile.includes(e.clientSearchQuery);return n&&r});return`
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 p-1 rounded-2xl bg-slate-900 border border-slate-800 w-fit">
          <button onclick="window.setClientSubTab('active')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all ${e.activeClientSubTab===`active`?`bg-blue-600 text-white shadow-md`:`text-slate-400 hover:text-white`}">
            Active Clients (${e.activeClientsCount})
          </button>
          <button onclick="window.setClientSubTab('inactive')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all ${e.activeClientSubTab===`inactive`?`bg-amber-600 text-white shadow-md`:`text-slate-400 hover:text-white`}">
            Inactive Clients (${e.inactiveClientsCount})
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5"></i>
            <input type="text" oninput="window.setClientSearch(this.value)" value="${e.clientSearchQuery}" placeholder="Search client..." class="pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:outline-none focus:border-blue-500 w-48 sm:w-64">
          </div>
          <button onclick="window.openModal('addClient')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
            <i data-lucide="plus" class="w-4 h-4"></i> + Add Client
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        ${t.length===0?`<div class="col-span-full text-center py-12 text-slate-500 text-sm">No clients found.</div>`:``}
        ${t.map(e=>`
          <div class="glass-panel rounded-3xl p-5 border border-slate-800 hover:border-blue-500/40 transition-all flex flex-col justify-between">
            <div>
              <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                  <h3 class="text-sm font-bold text-white hover:text-blue-400 cursor-pointer" onclick="window.openClientDetailModal(${e.id})">${e.name}</h3>
                  <p class="text-xs text-slate-400 mt-0.5">📱 ${e.mobile} ${e.mobile_secondary?`/ ${e.mobile_secondary}`:``}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${e.status===`active`?`bg-emerald-500/10 text-emerald-400 border border-emerald-500/20`:`bg-amber-500/10 text-amber-400 border border-amber-500/20`}">
                  ${e.status.toUpperCase()}
                </span>
              </div>

              <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800/80 space-y-1.5 text-xs text-slate-300 mb-4">
                <div class="flex justify-between">
                  <span class="text-slate-500">Monthly Package:</span>
                  <span class="font-bold text-white">${s(e.current_package)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Pending Due:</span>
                  <span class="font-bold ${parseFloat(e.total_due)>0?`text-rose-400`:`text-emerald-400`}">${s(e.total_due)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">Work Location:</span>
                  <span>${e.work_location||`—`}</span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-800/60">
              <button onclick="window.openRenewModal(${e.id}, '${e.name}', ${e.current_package})" class="flex-1 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1">
                🔄 Renew
              </button>
              <button onclick="window.openPaymentModal(${e.id}, '${e.name}', ${e.total_due>0?e.total_due:e.current_package})" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1">
                💰 Pay
              </button>
              <button onclick="window.openClientDetailModal(${e.id})" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold" title="View Profile">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
              ${e.status===`inactive`?`
                <button onclick="window.openDeleteClientModal(${e.id}, '${e.name}')" title="Delete Inactive Client" class="p-2 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              `:`
                <button onclick="window.toggleClientStatus(${e.id}, 'inactive')" title="Mark Inactive" class="p-2 bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                  <i data-lucide="pause-circle" class="w-4 h-4"></i>
                </button>
              `}
            </div>
          </div>
        `).join(``)}
      </div>
    </div>
  `}function g(){return`
    <div class="space-y-6">
      <h2 class="text-sm font-bold text-white">All Received Payments (${e.payments.length})</h2>
      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-800">
              <tr>
                <th class="p-4">Payment Date</th>
                <th class="p-4">Client</th>
                <th class="p-4">Amount</th>
                <th class="p-4">Payment Method</th>
                <th class="p-4">Received By</th>
                <th class="p-4">Notes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              ${e.payments.map(e=>`
                <tr class="hover:bg-slate-900/40 transition-all">
                  <td class="p-4 text-slate-300">${c(e.payment_date)}</td>
                  <td class="p-4">
                    <p class="font-bold text-white">${e.client_name}</p>
                    <p class="text-[10px] text-slate-500">${e.client_mobile}</p>
                  </td>
                  <td class="p-4 font-black text-emerald-400 text-sm">${s(e.amount)}</td>
                  <td class="p-4"><span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 uppercase">${e.payment_method}</span></td>
                  <td class="p-4 text-slate-400">${e.received_by_name||`Admin`}</td>
                  <td class="p-4 text-slate-400">${e.notes||`—`}</td>
                </tr>
              `).join(``)}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `}function _(){return`
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-white">Active Employees (${e.employees.length})</h2>
        <button onclick="window.openModal('addEmployee')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="user-plus" class="w-4 h-4"></i> + Add Employee
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        ${e.employees.map(e=>`
          <div class="glass-panel rounded-3xl p-5 border border-slate-800">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600/20 text-blue-400 font-black text-sm flex items-center justify-center border border-blue-500/30">
                  ${e.name.substring(0,2).toUpperCase()}
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
                <span class="font-bold text-blue-300 capitalize">${e.salary_type}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Fixed Salary:</span>
                <span class="font-bold text-white">${s(e.fixed_salary)}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Pending Advance:</span>
                <span class="font-bold text-rose-400">${s(e.pending_advance)}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Active Clients:</span>
                <span class="font-bold text-emerald-400">${e.active_clients_count||0}</span>
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
        `).join(``)}
      </div>
    </div>
  `}function v(){return`
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <select onchange="window.setSalaryMonth(this.value)" class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
            ${[`Jan`,`Feb`,`Mar`,`Apr`,`May`,`Jun`,`Jul`,`Aug`,`Sep`,`Oct`,`Nov`,`Dec`].map((t,n)=>`<option value="${n+1}" ${e.salaryMonth===n+1?`selected`:``}>${t}</option>`).join(``)}
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
              ${e.salaries.map(e=>`
                <tr class="hover:bg-slate-900/40 transition-all">
                  <td class="p-4">
                    <p class="font-bold text-white">${e.employee?.name||`—`}</p>
                    <p class="text-[10px] text-slate-500">${e.employee?.role_title||``}</p>
                  </td>
                  <td class="p-4 font-semibold text-slate-300">${s(e.base_salary)}</td>
                  <td class="p-4 font-semibold text-blue-400">${s(e.total_commission)}</td>
                  <td class="p-4 font-semibold text-amber-400">-${s(e.advance_deducted)}</td>
                  <td class="p-4 font-semibold text-rose-400">-${s(e.other_deductions)}</td>
                  <td class="p-4 font-bold text-emerald-400 text-sm">${s(e.net_payable)}</td>
                  <td class="p-4">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${e.status===`paid`?`bg-emerald-500/10 text-emerald-400 border border-emerald-500/20`:`bg-amber-500/10 text-amber-400 border border-amber-500/20`}">
                      ${e.status?e.status.toUpperCase():`PENDING`}
                    </span>
                  </td>
                  <td class="p-4 text-right space-x-1.5">
                    ${e.status===`paid`?`
                      <span class="text-xs text-slate-500 font-semibold">✅ Paid</span>
                    `:`
                      <button onclick="window.markSalaryPaid(${e.employee_id}, ${e.net_payable})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm">
                        💰 Mark Paid
                      </button>
                    `}
                    <button onclick="window.openCutSalaryModal(${e.employee_id}, '${e.employee?.name}')" class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-xl text-xs font-bold">
                      ✂️ Cut
                    </button>
                  </td>
                </tr>
              `).join(``)}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `}function y(){return`
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">General Expenses:</span>
            <span class="font-bold text-white ml-1.5">${s(e.expenseTotals.totalGeneral)}</span>
          </div>
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">Paid Salaries:</span>
            <span class="font-bold text-blue-400 ml-1.5">${s(e.expenseTotals.totalSalaryPaid)}</span>
          </div>
          <div class="px-3.5 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
            <span class="text-slate-400">Deductible in Bachat:</span>
            <span class="font-bold text-purple-400 ml-1.5">${s(e.expenseTotals.totalDeductible)}</span>
          </div>
        </div>

        <button onclick="window.openModal('addExpense')" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-md">
          <i data-lucide="plus" class="w-4 h-4"></i> + Add Expense
        </button>
      </div>

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
              ${e.expenses.map(e=>`
                <tr class="hover:bg-slate-900/40 transition-all ${e.include_in_calculation?``:`opacity-50`}">
                  <td class="p-4">
                    ${e.entry_type===`general`?`
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" onchange="window.toggleExpenseCalculation(${e.id}, this.checked)" ${e.include_in_calculation?`checked`:``} class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-600">
                        <span class="text-[11px] text-slate-400">${e.include_in_calculation?`Included`:`Excluded`}</span>
                      </label>
                    `:`<span class="text-[11px] text-blue-400 font-semibold">Auto-Counted</span>`}
                  </td>
                  <td class="p-4 text-slate-400">${c(e.expense_date)}</td>
                  <td class="p-4">
                    <p class="font-bold text-white">${e.title}</p>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 border border-slate-700">${e.category_name||`General`}</span>
                  </td>
                  <td class="p-4 font-black text-rose-400 text-sm">${s(e.amount)}</td>
                  <td class="p-4 text-slate-400">${e.notes||`—`}</td>
                  <td class="p-4 text-right">
                    ${e.entry_type===`general`?`
                      <button onclick="window.deleteExpense(${e.id})" class="p-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded-lg transition-all">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                      </button>
                    `:`—`}
                  </td>
                </tr>
              `).join(``)}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `}function b(){return`
    <div class="space-y-6">
      <h2 class="text-sm font-bold text-white">Client Work Assignments</h2>
      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-800">
              <tr>
                <th class="p-4">Client</th>
                <th class="p-4">Assigned Employee</th>
                <th class="p-4">Assigned Date</th>
                <th class="p-4">Location</th>
                <th class="p-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              ${e.workTracker.map(e=>`
                <tr class="hover:bg-slate-900/40 transition-all">
                  <td class="p-4">
                    <p class="font-bold text-white">${e.client_name}</p>
                    <p class="text-[10px] text-slate-500">${e.client_mobile}</p>
                  </td>
                  <td class="p-4 font-semibold text-blue-300">${e.employee_name}</td>
                  <td class="p-4 text-amber-400 font-semibold">${c(e.assigned_date)}</td>
                  <td class="p-4 text-slate-400">${e.work_location||`—`}</td>
                  <td class="p-4 text-right">
                    <button onclick="window.openWorkHistoryModal(${e.client_id}, '${e.client_name}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold">
                      📅 Month History
                    </button>
                  </td>
                </tr>
              `).join(``)}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `}function x(){return`
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-white">Investors Group (${e.investors.length})</h2>
        <button onclick="window.openModal('addInvestor')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold">
          + Add Investor
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        ${e.investors.map(e=>`
          <div class="glass-panel rounded-3xl p-5 border border-slate-800">
            <h3 class="text-sm font-bold text-white">${e.name}</h3>
            <p class="text-xs text-slate-400 mt-1">${e.phone} • ${e.email||`—`}</p>
            <div class="mt-3 p-3 bg-slate-900 rounded-2xl flex justify-between text-xs">
              <span class="text-slate-400">Total Capital Invested:</span>
              <span class="font-bold text-emerald-400">${s(e.total_invested)}</span>
            </div>
          </div>
        `).join(``)}
      </div>
    </div>
  `}function S(){let t=e.reports||{};return`
    <div class="space-y-6">
      <h2 class="text-sm font-bold text-white">Financial & Profit Overview</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-emerald-500/20">
          <span class="text-xs text-slate-400">Total Lifetime Collections</span>
          <p class="text-2xl font-black text-emerald-400 mt-2">${s(t.totalCollections)}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-rose-500/20">
          <span class="text-xs text-slate-400">Total Lifetime Expenses</span>
          <p class="text-2xl font-black text-rose-400 mt-2">${s(t.totalExpenses)}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-amber-500/20">
          <span class="text-xs text-slate-400">Total Lifetime Salaries Paid</span>
          <p class="text-2xl font-black text-amber-400 mt-2">${s(t.totalSalaries)}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-blue-500/20">
          <span class="text-xs text-slate-400">Net Business Profit</span>
          <p class="text-2xl font-black text-blue-400 mt-2">${s(t.netProfit)}</p>
        </div>
      </div>
    </div>
  `}function C(){return`
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-white">System User Accounts (${e.systemUsers.length})</h2>
        <button onclick="window.openModal('addUser')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold">
          + Add User Account
        </button>
      </div>

      <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-900/90 text-slate-400 font-bold border-b border-slate-800">
            <tr>
              <th class="p-4">Name</th>
              <th class="p-4">Username</th>
              <th class="p-4">Email</th>
              <th class="p-4">Role</th>
              <th class="p-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            ${e.systemUsers.map(e=>`
              <tr class="hover:bg-slate-900/40">
                <td class="p-4 font-bold text-white">${e.name}</td>
                <td class="p-4 text-slate-300">${e.username}</td>
                <td class="p-4 text-slate-400">${e.email||`—`}</td>
                <td class="p-4"><span class="px-2 py-0.5 rounded-full text-[10px] bg-blue-900/60 text-blue-300 border border-blue-700">${e.role_name||`Admin`}</span></td>
                <td class="p-4 text-emerald-400 font-semibold">${e.status}</td>
              </tr>
            `).join(``)}
          </tbody>
        </table>
      </div>
    </div>
  `}function w(){return e.activeModal?`
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm overflow-y-auto">
      <div class="glass-panel bg-slate-900 rounded-3xl max-w-lg w-full p-6 border border-slate-700 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="window.closeModal()" class="absolute top-6 right-6 text-slate-400 hover:text-white font-bold text-xl">&times;</button>
        ${T()}
      </div>
    </div>
  `:``}function T(){let t=e.activeModal,n=e.modalPayload||{};if(t===`addClient`)return`
      <h3 class="text-base font-bold text-white mb-4">Add New Client</h3>
      <form id="addClientForm" class="space-y-3.5 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Client Name *</label>
          <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Primary Mobile *</label>
            <input type="text" name="mobile" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Secondary Mobile</label>
            <input type="text" name="mobile_secondary" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Monthly Package (₹) *</label>
            <input type="number" name="current_package" required value="5000" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none font-bold">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">GST Count</label>
            <input type="number" name="gst_count" value="1" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Service Start Date *</label>
            <input type="date" name="service_start_date" value="${new Date().toISOString().split(`T`)[0]}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Work Location</label>
            <input type="text" name="work_location" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white outline-none">
          </div>
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Manager</label>
          <select name="manager_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white">
            <option value="">Select Manager</option>
            ${e.managers.map(e=>`<option value="${e.id}">${e.name}</option>`).join(``)}
          </select>
        </div>
        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg mt-4">Save Client</button>
      </form>
    `;if(t===`renewClient`){let e=new Date().toISOString().split(`T`)[0],t=new Date(new Date().setMonth(new Date().getMonth()+1)).toISOString().split(`T`)[0];return`
      <h3 class="text-base font-bold text-white mb-2">🔄 Renew Client — ${n.clientName}</h3>
      <form id="renewClientForm" class="space-y-4 text-xs">
        <div class="p-3 bg-blue-950/40 rounded-xl border border-blue-800/40">
          <label class="block font-semibold text-slate-300 mb-2">Package Renewal Option:</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-2 p-2 bg-slate-900 rounded-lg cursor-pointer">
              <input type="radio" name="package_option" value="same" checked onchange="document.getElementById('customPkgBox').style.display='none'">
              <span>Same (₹${n.packageAmount})</span>
            </label>
            <label class="flex items-center gap-2 p-2 bg-slate-900 rounded-lg cursor-pointer">
              <input type="radio" name="package_option" value="new" onchange="document.getElementById('customPkgBox').style.display='block'">
              <span>New Package</span>
            </label>
          </div>
          <div id="customPkgBox" class="mt-3 hidden">
            <label class="block font-semibold text-slate-300 mb-1">New Package Amount (₹)</label>
            <input type="number" name="custom_package_amount" value="${n.packageAmount}" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Start Date</label>
            <input type="date" name="start_date" value="${e}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
          </div>
          <div>
            <label class="block font-semibold text-slate-300 mb-1">End Date</label>
            <input type="date" name="end_date" value="${t}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-white">
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
              <input type="number" name="payment_amount" value="${n.packageAmount}" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white">
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

        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg mt-4">Confirm Renewal</button>
      </form>
    `}return t===`receivePayment`?`
      <h3 class="text-base font-bold text-white mb-2">💰 Receive Payment — ${n.clientName}</h3>
      <form id="receivePaymentForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Amount (₹) *</label>
          <input type="number" name="amount" required value="${n.amount}" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-300 mb-1">Payment Date *</label>
            <input type="date" name="payment_date" value="${new Date().toISOString().split(`T`)[0]}" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
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
    `:t===`cutSalary`?`
      <h3 class="text-base font-bold text-white mb-2">✂️ Cut Employee Salary</h3>
      <p class="text-xs text-slate-400 mb-4">Employee: <strong class="text-white">${n.employeeName}</strong></p>
      <form id="cutSalaryForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Cut Amount (₹) *</label>
          <input type="number" name="amount" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold">
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Reason for Deduction *</label>
          <textarea name="reason" required rows="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white" placeholder="e.g. Penalty for work delay or 2 absent days"></textarea>
        </div>
        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg mt-4">Apply Deduction</button>
      </form>
    `:t===`giveAdvance`?`
      <h3 class="text-base font-bold text-white mb-4">💸 Disburse Salary Advance</h3>
      <form id="giveAdvanceForm" class="space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Select Employee *</label>
          <select name="employee_id" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
            ${e.employees.map(e=>`<option value="${e.id}" ${n.employeeId===e.id?`selected`:``}>${e.name}</option>`).join(``)}
          </select>
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Advance Amount (₹) *</label>
          <input type="number" name="amount" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold">
        </div>
        <div>
          <label class="block font-semibold text-slate-300 mb-1">Reason</label>
          <input type="text" name="reason" placeholder="Personal advance" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
        </div>
        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg mt-4">Disburse Advance</button>
      </form>
    `:t===`addExpense`?`
      <h3 class="text-base font-bold text-white mb-4">Add General Expense</h3>
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
            <input type="date" name="expense_date" value="${new Date().toISOString().split(`T`)[0]}" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white">
          </div>
        </div>
        <div>
          <label class="flex items-center gap-2 cursor-pointer mt-2">
            <input type="checkbox" name="include_in_calculation" checked class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-600">
            <span class="text-slate-300 font-semibold">Include in Monthly Bachat calculation</span>
          </label>
        </div>
        <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl shadow-lg mt-4">Save Expense</button>
      </form>
    `:t===`deleteClient`?`
      <h3 class="text-base font-bold text-rose-400 mb-2 flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-5 h-5"></i> Delete Inactive Client
      </h3>
      <p class="text-xs text-slate-300 mb-4">
        Are you sure you want to permanently delete inactive client <strong class="text-white">${n.clientName}</strong>?
      </p>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="window.closeModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">Cancel</button>
        <button onclick="window.confirmDeleteClient(${n.clientId})" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold">Yes, Delete</button>
      </div>
    `:``}function E(){if(!e.toast)return``;let t=e.toast.type===`error`;return`
    <div class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-2xl text-xs font-bold ${t?`bg-rose-600 text-white shadow-rose-600/30`:`bg-emerald-600 text-white shadow-emerald-600/30`} animate-bounce">
      <i data-lucide="${t?`alert-circle`:`check-circle`}" class="w-4 h-4"></i>
      <span>${e.toast.message}</span>
    </div>
  `}function D(){let e=document.getElementById(`loginForm`);e&&e.addEventListener(`submit`,e=>{e.preventDefault();let t=document.getElementById(`loginUsername`).value,n=document.getElementById(`loginPassword`).value;r(t,n)})}function O(){let r=document.getElementById(`addClientForm`);r&&r.addEventListener(`submit`,async e=>{e.preventDefault();let i=new FormData(r);try{await t(`/api/clients`,{method:`POST`,body:JSON.stringify(Object.fromEntries(i))}),n(`Client added successfully!`),closeModal(),a()}catch{}});let i=document.getElementById(`renewClientForm`);i&&i.addEventListener(`submit`,async r=>{r.preventDefault();let o=new FormData(i),s=Object.fromEntries(o);s.collect_payment=o.get(`collect_payment`)===`on`;try{await t(`/api/clients/${e.modalPayload.clientId}/renew`,{method:`POST`,body:JSON.stringify(s)}),n(`Client renewed successfully!`),closeModal(),a()}catch{}});let o=document.getElementById(`receivePaymentForm`);o&&o.addEventListener(`submit`,async r=>{r.preventDefault();let i=new FormData(o);try{await t(`/api/clients/${e.modalPayload.clientId}/payments`,{method:`POST`,body:JSON.stringify(Object.fromEntries(i))}),n(`Payment recorded successfully!`),closeModal(),a()}catch{}});let s=document.getElementById(`cutSalaryForm`);s&&s.addEventListener(`submit`,async r=>{r.preventDefault();let i=new FormData(s);try{await t(`/api/employees/${e.modalPayload.employeeId}/deductions`,{method:`POST`,body:JSON.stringify(Object.fromEntries(i))}),n(`Salary cut applied & employee notified!`),closeModal(),a()}catch{}});let c=document.getElementById(`giveAdvanceForm`);c&&c.addEventListener(`submit`,async e=>{e.preventDefault();let r=new FormData(c);try{await t(`/api/salary/advance`,{method:`POST`,body:JSON.stringify(Object.fromEntries(r))}),n(`Advance disbursed successfully!`),closeModal(),a()}catch{}});let l=document.getElementById(`addExpenseForm`);l&&l.addEventListener(`submit`,async e=>{e.preventDefault();let r=new FormData(l),i=Object.fromEntries(r);i.include_in_calculation=r.get(`include_in_calculation`)===`on`;try{await t(`/api/expenses`,{method:`POST`,body:JSON.stringify(i)}),n(`Expense recorded successfully!`),closeModal(),a()}catch{}})}window.switchTab=o,window.logout=i,window.openModal=(t,n={})=>{e.activeModal=t,e.modalPayload=n,l()},window.closeModal=()=>{e.activeModal=null,e.modalPayload={},l()},window.setClientSubTab=t=>{e.activeClientSubTab=t,l()},window.setClientSearch=t=>{e.clientSearchQuery=t,l()},window.openRenewModal=(e,t,n)=>{window.openModal(`renewClient`,{clientId:e,clientName:t,packageAmount:n})},window.openPaymentModal=(e,t,n)=>{window.openModal(`receivePayment`,{clientId:e,clientName:t,amount:n})},window.openCutSalaryModal=(e,t)=>{window.openModal(`cutSalary`,{employeeId:e,employeeName:t})},window.openGiveAdvanceModal=(e,t)=>{window.openModal(`giveAdvance`,{employeeId:e,employeeName:t})},window.openDeleteClientModal=(e,t)=>{window.openModal(`deleteClient`,{clientId:e,clientName:t})},window.confirmDeleteClient=async e=>{try{await t(`/api/clients/${e}`,{method:`DELETE`}),n(`Inactive client deleted successfully!`),closeModal(),a()}catch{}},window.toggleClientStatus=async(e,r)=>{try{await t(`/api/clients/${e}/status`,{method:`PUT`,body:JSON.stringify({status:r})}),n(`Client marked as ${r}!`),a()}catch{}},window.setSalaryMonth=t=>{e.salaryMonth=parseInt(t),a()},window.setSalaryYear=t=>{e.salaryYear=parseInt(t),a()},window.markSalaryPaid=async(r,i)=>{try{await t(`/api/salary/pay`,{method:`POST`,body:JSON.stringify({employee_id:r,month:e.salaryMonth,year:e.salaryYear,amount:i})}),n(`Salary marked as paid!`),a()}catch{}},window.toggleExpenseCalculation=async(e,r)=>{try{await t(`/api/expenses/${e}/toggle-calculation`,{method:`PATCH`,body:JSON.stringify({include_in_calculation:r})}),n(`Expense calculation flag updated!`),a()}catch{}},window.deleteExpense=async e=>{try{await t(`/api/expenses/${e}`,{method:`DELETE`}),n(`Expense deleted!`),a()}catch{}},e.token?a():l()}))();