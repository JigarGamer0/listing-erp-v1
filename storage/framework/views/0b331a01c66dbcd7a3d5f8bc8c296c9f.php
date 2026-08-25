<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="fade-in" x-data="{
    showDragPaymentModal: false,
    showExpenseDeductionModal: false,
    dragSearch: '',
    selectedClient: null,
    cycles: [],
    selectedCycleId: '',
    showCycleField: false,
    paymentAmount: 0,
    paymentDate: '<?php echo e(date('Y-m-d')); ?>',
    paymentMethod: 'cash',
    paymentNotes: '',
    clientsList: <?php echo e(json_encode($allClients->map(function($c) { return ['id' => $c->id, 'name' => $c->name, 'mobile' => $c->mobile, 'total_due' => $c->total_due, 'billing_cycles' => $c->billingCycles->map(function($bc) { return ['id' => $bc->id, 'start' => $bc->billing_start->format('d M Y'), 'end' => $bc->billing_end->format('d M Y'), 'status' => $bc->status, 'due_date' => $bc->billing_end->addDays(5)->format('Y-m-d')]; })]; }))); ?>,
    
    expensesList: <?php echo e(json_encode($allMonthlyExpenses->map(function($exp) { 
        return [
            'id' => $exp->id,
            'title' => $exp->title,
            'category' => $exp->category?->name ?? 'General',
            'amount' => (float)$exp->amount,
            'date' => $exp->expense_date ? $exp->expense_date->format('d M Y') : '—',
            'included' => (bool)($exp->include_in_calculation ?? true)
        ]; 
    }))); ?>,
    payrollPayable: <?php echo e((float)$totalSalaryPayableThisMonth); ?>,
    totalReceivables: <?php echo e((float)$paymentDue); ?>,

    get activeMonthlyExpenses() {
        return this.expensesList.filter(e => e.included).reduce((sum, e) => sum + e.amount, 0);
    },

    get netProjectedSavings() {
        return this.totalReceivables - this.payrollPayable - this.activeMonthlyExpenses;
    },

    toggleExpenseDeduction(expense) {
        const oldState = expense.included;
        expense.included = !oldState;

        fetch(`/expenses/${expense.id}/toggle-calculation`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                expense.included = oldState;
            }
        })
        .catch(() => {
            expense.included = oldState;
        });
    },
    
    get filteredClients() {
        if (!this.dragSearch) return this.clientsList;
        const q = this.dragSearch.toLowerCase();
        return this.clientsList.filter(c => c.name.toLowerCase().includes(q) || c.mobile.includes(q));
    },
    
    selectClient(client) {
        this.selectedClient = client;
        this.cycles = client.billing_cycles || [];
        this.paymentAmount = client.total_due || 0;
        this.selectedCycleId = '';
        this.showCycleField = false;

        const today = new Date().toISOString().split('T')[0];
        const pendingCycleAfterDueDate = this.cycles.find(bc => {
            return (bc.status === 'pending' || bc.status === 'partial' || bc.status === 'overdue') && today > bc.due_date;
        });

        if (pendingCycleAfterDueDate) {
            this.showCycleField = true;
            this.selectedCycleId = pendingCycleAfterDueDate.id;
        }
    },
    
    handleDragStart(e, client) {
        e.dataTransfer.setData('text/plain', JSON.stringify(client));
    },
    
    handleDrop(e) {
        try {
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            if (data && data.id) {
                this.selectClient(data);
            }
        } catch(err) {
            console.error('Error handling drop select:', err);
        }
    }
}">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Welcome back, <?php echo e(auth()->user()->name); ?> 👋</p>
        </div>
        <div class="flex items-center space-x-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg text-xs text-gray-500 font-medium shadow-sm">
            <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span><?php echo e(now()->startOfMonth()->format('d M Y')); ?> - <?php echo e(now()->endOfMonth()->format('d M Y')); ?></span>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-amber-500 uppercase tracking-wider">Total Receivables</p>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">₹<?php echo e(number_format($paymentDue, 0)); ?></p>
                </div>
                <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500">
                <span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-600 font-semibold"><?php echo e($activeDueClientsCount); ?> Active Clients</span>
                <span class="ml-1.5 font-medium">pending due</span>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Monthly Payroll Payable</p>
                    <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">₹<?php echo e(number_format($totalSalaryPayableThisMonth, 0)); ?></p>
                </div>
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>Fixed + Commission: <strong class="text-gray-700 dark:text-gray-300">₹<?php echo e(number_format($totalCommissionThisMonth, 0)); ?></strong></span>
            </div>
        </div>

        
        <div @click="showExpenseDeductionModal = true" class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden cursor-pointer hover:border-rose-300 dark:hover:border-rose-700 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-rose-500 uppercase tracking-wider flex items-center gap-1">
                        <span>Monthly Expenses</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-normal">⚙️ Manage</span>
                    </p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1" x-text="'₹' + Number(activeMonthlyExpenses).toLocaleString('en-IN')">
                        ₹<?php echo e(number_format($monthlyExpenses, 0)); ?>

                    </p>
                </div>
                <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-xl flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span class="text-rose-500 font-semibold group-hover:underline">Click to Tick / Untick Expenses →</span>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Net Projected Savings</p>
                    <p class="text-2xl font-extrabold mt-1" 
                       :class="netProjectedSavings >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600'" 
                       x-text="'₹' + Number(netProjectedSavings).toLocaleString('en-IN')">
                        ₹<?php echo e(number_format($netProjectedBachat, 0)); ?>

                    </p>
                </div>
                <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-[11px] text-gray-400 font-medium">
                <span>Receivables − Payroll − Active Expenses</span>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Available Cash Balance</p>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">₹<?php echo e(number_format($availableFund, 0)); ?></p>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-clear-fund-modal'))" class="text-[10px] text-indigo-600 hover:underline mt-2 font-medium flex items-center gap-0.5 outline-none">
                        🧹 Clear Fund by Owner
                    </button>
                </div>
                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-500 font-medium">
                <span>Total Collection: <strong class="text-gray-700 dark:text-gray-300">₹<?php echo e(number_format($monthlyCollection, 0)); ?></strong></span>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3 flex-1">
                <a href="<?php echo e(route('clients.create')); ?>" class="flex flex-col items-center justify-center p-4 bg-blue-50/50 hover:bg-blue-50 dark:bg-blue-900/10 dark:hover:bg-blue-900/20 rounded-xl transition-all border border-blue-100/50 dark:border-blue-900/20 group">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">Add Client</span>
                </a>
                <button @click="showDragPaymentModal = true" class="flex flex-col items-center justify-center p-4 bg-emerald-50/50 hover:bg-emerald-50 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/20 rounded-xl transition-all border border-emerald-100/50 dark:border-emerald-900/20 group w-full text-center">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 mb-2 group-hover:scale-105 transition-transform mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Receive Payment</span>
                </button>
                <a href="<?php echo e(route('employees.create')); ?>" class="flex flex-col items-center justify-center p-4 bg-indigo-50/50 hover:bg-indigo-50 dark:bg-indigo-900/10 dark:hover:bg-indigo-900/20 rounded-xl transition-all border border-indigo-100/50 dark:border-indigo-900/20 group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-400">Add Employee</span>
                </a>
                <a href="<?php echo e(route('expenses.create')); ?>" class="flex flex-col items-center justify-center p-4 bg-amber-50/50 hover:bg-amber-50 dark:bg-amber-900/10 dark:hover:bg-amber-900/20 rounded-xl transition-all border border-amber-100/50 dark:border-amber-900/20 group">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Add Expense</span>
                </a>
                <a href="<?php echo e(route('salary.advance.form')); ?>" class="flex flex-col items-center justify-center p-4 bg-purple-50/50 hover:bg-purple-50 dark:bg-purple-900/10 dark:hover:bg-purple-900/20 rounded-xl transition-all border border-purple-100/50 dark:border-purple-900/20 group">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-600 mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-purple-700 dark:text-purple-400">Salary Advance</span>
                </a>
                <a href="<?php echo e(route('salary.index')); ?>" class="flex flex-col items-center justify-center p-4 bg-rose-50/50 hover:bg-rose-50 dark:bg-rose-900/10 dark:hover:bg-rose-900/20 rounded-xl transition-all border border-rose-100/50 dark:border-rose-900/20 group">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-600 mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">Pay Salary</span>
                </a>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Upcoming Renewals</h3>
                <a href="<?php echo e(route('clients.index')); ?>" class="text-xs text-blue-600 hover:underline">View All</a>
            </div>
            <div class="space-y-3 flex-1">
                <?php $__empty_1 = true; $__currentLoopData = $paymentDueClients->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50">
                        <div>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200"><?php echo e($client->name); ?></p>
                            <p class="text-[10px] text-gray-400">Due Date: <?php echo e(now()->addDays(5)->format('d M Y')); ?></p>
                        </div>
                        <span class="text-xs font-bold text-amber-600">₹<?php echo e(number_format($client->total_due, 0)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-xs text-gray-400 py-6">No renewals upcoming</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Revenue vs Expense</h3>
                <span class="text-[10px] text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600">This Month</span>
            </div>
            <div class="h-56">
                <canvas id="collectionExpenseChart"></canvas>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Client Growth</h3>
                <span class="text-[10px] text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600">This Month</span>
            </div>
            <div class="h-56">
                <canvas id="clientGrowthChart"></canvas>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Recent Activity</h3>
                <div class="space-y-4 max-h-48 overflow-y-auto pr-1">
                    <?php $__empty_1 = true; $__currentLoopData = $recentActivities->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 flex-shrink-0"></div>
                            <div>
                                <p class="text-gray-700 dark:text-gray-300 font-medium"><?php echo e($activity->description); ?></p>
                                <p class="text-[10px] text-gray-400 mt-0.5"><?php echo e($activity->created_at->diffForHumans()); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-xs text-gray-400 py-12">No recent activity</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-50 dark:border-gray-700/50 mt-3">
                <a href="<?php echo e(route('activity-logs.index')); ?>" class="w-full inline-flex justify-center items-center py-2 bg-gray-50 dark:bg-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    View All Activities
                </a>
            </div>
        </div>
    </div>

    
    <div x-show="showDragPaymentModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-lg w-full mx-4 shadow-xl relative"
             @click.away="showDragPaymentModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Receive Payment</h3>
                <button type="button" @click="showDragPaymentModal = false" class="text-gray-400 hover:text-gray-650 dark:hover:text-gray-300 text-xl font-bold">&times;</button>
            </div>

            <!-- Client Selection Step -->
            <div x-show="!selectedClient" class="space-y-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search and Select Client</label>
                <input type="text" x-model="dragSearch" placeholder="Type client name or mobile..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none">
                
                <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 border border-gray-150 dark:border-gray-700 rounded-xl">
                    <template x-for="c in filteredClients" :key="c.id">
                        <button type="button" @click="selectClient(c)" 
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex justify-between items-center transition-all">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white" x-text="c.name"></p>
                                <p class="text-xs text-gray-500" x-text="c.mobile"></p>
                            </div>
                            <span class="text-xs font-bold text-red-600" x-text="'₹' + parseFloat(c.total_due).toLocaleString('en-IN')"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Payment Details Form Step -->
            <div x-show="selectedClient">
                <template x-if="selectedClient">
                    <form method="POST" :action="'/clients/' + selectedClient.id + '/receive-payment'">
                        <?php echo csrf_field(); ?>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 mb-4 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-gray-850 dark:text-white" x-text="selectedClient.name"></p>
                                <p class="text-xs text-gray-500" x-text="selectedClient.mobile"></p>
                            </div>
                            <div class="text-right">
                                <button type="button" @click="selectedClient = null" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Change Client</button>
                                <p class="text-sm font-bold text-red-600" x-text="'Due: ₹' + parseFloat(selectedClient.total_due).toLocaleString('en-IN')"></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (₹) *</label>
                                <input type="number" name="amount" x-model="paymentAmount" required step="0.01" min="1"
                                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-lg font-bold">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                                    <input type="date" name="payment_date" x-model="paymentDate" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Method *</label>
                                    <select name="payment_method" x-model="paymentMethod" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="showCycleField">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apply to Cycle</label>
                                <select name="billing_cycle_id" x-model="selectedCycleId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                                    <option value="">— Automatic Allocation —</option>
                                    <template x-for="bc in cycles" :key="bc.id">
                                        <option :value="bc.id" x-text="bc.start + ' — ' + bc.end + ' (' + bc.status + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                <textarea name="notes" x-model="paymentNotes" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showDragPaymentModal = false" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">💰 Receive Payment</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    
    <div x-show="showExpenseDeductionModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" 
         x-transition 
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full border border-gray-100 dark:border-gray-700 shadow-2xl p-6 relative overflow-hidden" 
             @click.away="showExpenseDeductionModal = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-2.5">
                    <span class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 flex items-center justify-center font-bold">💳</span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Monthly Expense Deductions</h3>
                        <p class="text-xs text-gray-500">Tick/untick expenses to include or exclude from this month's calculation</p>
                    </div>
                </div>
                <button type="button" @click="showExpenseDeductionModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            
            <div class="grid grid-cols-3 gap-3 mb-5 p-3.5 bg-gray-50 dark:bg-gray-750/40 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Total Logged</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white mt-0.5" x-text="'₹' + Number(expensesList.reduce((sum, e) => sum + e.amount, 0)).toLocaleString('en-IN')"></p>
                </div>
                <div>
                    <p class="text-[10px] text-rose-500 uppercase font-bold">Deducted This Month</p>
                    <p class="text-sm font-extrabold text-rose-600 dark:text-rose-400 mt-0.5" x-text="'₹' + Number(activeMonthlyExpenses).toLocaleString('en-IN')"></p>
                </div>
                <div>
                    <p class="text-[10px] text-emerald-500 uppercase font-bold">Projected Net Savings</p>
                    <p class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5" x-text="'₹' + Number(netProjectedSavings).toLocaleString('en-IN')"></p>
                </div>
            </div>

            
            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60 pr-1 space-y-1">
                <template x-for="expense in expensesList" :key="expense.id">
                    <div class="py-3 px-3 flex items-center justify-between hover:bg-gray-50/70 dark:hover:bg-gray-700/30 rounded-xl transition-all border border-transparent hover:border-gray-100 dark:hover:border-gray-700">
                        <div class="flex items-center space-x-3.5">
                            <input type="checkbox" 
                                   :checked="expense.included" 
                                   @change="toggleExpenseDeduction(expense)" 
                                   class="w-5 h-5 text-rose-600 rounded border-gray-300 dark:border-gray-600 focus:ring-rose-500 cursor-pointer">
                            <div>
                                <p class="text-xs font-bold text-gray-900 dark:text-white" :class="!expense.included ? 'line-through text-gray-400 dark:text-gray-500' : ''" x-text="expense.title"></p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    <span x-text="expense.category"></span> · <span x-text="expense.date"></span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right flex items-center space-x-3">
                            <span class="text-xs font-extrabold text-gray-900 dark:text-white" x-text="'₹' + Number(expense.amount).toLocaleString('en-IN')"></span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold transition-all"
                                  :class="expense.included ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                                  x-text="expense.included ? '✓ Deducted' : '⏸️ Unticked (Next Month)'">
                            </span>
                        </div>
                    </div>
                </template>

                <template x-if="expensesList.length === 0">
                    <div class="py-8 text-center text-xs text-gray-400">
                        No expenses logged for this month.
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="showExpenseDeductionModal = false" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-md transition-all">
                    Done / Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const collectionData = <?php echo json_encode($monthlyCollectionData, 15, 512) ?>;
    const expenseData = <?php echo json_encode($monthlyExpenseData, 15, 512) ?>;
    const profitData = <?php echo json_encode($monthlyProfitData, 15, 512) ?>;

    // Collection vs Expense Chart
    new Chart(document.getElementById('collectionExpenseChart'), {
        type: 'line',
        data: {
            labels: collectionData.map(d => d.label),
            datasets: [
                { 
                    label: 'Revenue', 
                    data: collectionData.map(d => d.value), 
                    borderColor: '#2563EB', 
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5
                },
                { 
                    label: 'Expense', 
                    data: expenseData.map(d => d.value), 
                    borderColor: '#EF4444', 
                    backgroundColor: 'rgba(239, 68, 68, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5
                }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    grid: { color: 'rgba(243, 244, 246, 0.6)' },
                    ticks: { color: '#9CA3AF', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9CA3AF', font: { size: 10 } }
                }
            }, 
            plugins: { 
                legend: { position: 'top', align: 'end', labels: { boxWidth: 12, font: { size: 11 } } } 
            } 
        }
    });

    // Client Growth Chart
    new Chart(document.getElementById('clientGrowthChart'), {
        type: 'line',
        data: {
            labels: profitData.map(d => d.label),
            datasets: [{
                label: 'Clients',
                data: profitData.map(d => d.value),
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                fill: true, 
                tension: 0.4, 
                borderWidth: 2.5, 
                pointRadius: 0
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                y: { 
                    grid: { color: 'rgba(243, 244, 246, 0.6)' },
                    ticks: { color: '#9CA3AF', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9CA3AF', font: { size: 10 } }
                }
            }, 
            plugins: { 
                legend: { display: false } 
            } 
        }
    });
});
</script>


<div id="clear-fund-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-xl relative">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Clear Available Fund by Owner</h3>
        
        <form method="POST" action="<?php echo e(route('fund.clear')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                    <p class="text-xs text-gray-500">Current Available Fund</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">₹<?php echo e(number_format($availableFund, 2)); ?></p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clearing Option</label>
                    
                    <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750/20">
                        <input type="radio" name="mode" value="all" checked class="w-4 h-4 text-indigo-600">
                        <div>
                            <span class="block text-sm font-medium text-gray-800 dark:text-white">Clear All Funds</span>
                            <span class="text-xs text-gray-500">Deduct the entire available fund balance</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750/20">
                        <input type="radio" name="mode" value="custom" class="w-4 h-4 text-indigo-600">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-800 dark:text-white">Clear Custom Amount</span>
                            <span class="text-xs text-gray-500">Deduct a specific amount</span>
                            <div class="mt-2 hidden" id="clear_custom_amount_container">
                                <input type="number" step="0.01" min="0.01" max="<?php echo e($availableFund); ?>" name="amount" id="clear_custom_amount" placeholder="Enter amount (e.g. 5000)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-650 rounded-xl text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none">
                            </div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date</label>
                    <input type="date" name="date" value="<?php echo e(date('Y-m-d')); ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Notes (Optional)</label>
                    <textarea name="notes" placeholder="e.g. Cleared by owners" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-800 dark:text-white outline-none text-sm" rows="2"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" id="clear_fund_modal_cancel" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-300 transition-all text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">Clear Fund</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('open-clear-fund-modal', function() {
        const modal = document.getElementById('clear-fund-modal');
        modal.classList.remove('hidden');
    });

    document.getElementById('clear_fund_modal_cancel').addEventListener('click', function() {
        const modal = document.getElementById('clear-fund-modal');
        modal.classList.add('hidden');
    });

    const clearModeRadios = document.getElementsByName('mode');
    const clearCustomAmountContainer = document.getElementById('clear_custom_amount_container');
    const clearCustomAmountInput = document.getElementById('clear_custom_amount');

    clearModeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                clearCustomAmountContainer.classList.remove('hidden');
                clearCustomAmountInput.setAttribute('required', 'required');
            } else {
                clearCustomAmountContainer.classList.add('hidden');
                clearCustomAmountInput.removeAttribute('required');
                clearCustomAmountInput.value = '';
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jigar\OneDrive\Documents\.l\Listing website v1\resources\views/dashboard.blade.php ENDPATH**/ ?>