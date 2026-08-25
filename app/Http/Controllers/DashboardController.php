<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\ClientBillingCycle;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Expense;
use App\Models\FollowUp;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('Employee')) {
            return redirect()->route('employee.dashboard');
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // ─── Financial Stat Calculations ─────────────────────────
        // 1. Kitna Paisa Lena Hai (Active clients pending due)
        $paymentDue = ClientBillingCycle::whereHas('client', function ($q) {
            $q->where('status', 'active');
        })->whereIn('status', ['pending', 'partial', 'overdue'])->sum('balance');

        $activeDueClientsCount = Client::where('status', 'active')->paymentDue()->count();

        // 2. This Month Collection
        $monthlyCollection = ClientPayment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $todayCollection = ClientPayment::whereDate('payment_date', today())->sum('amount');

        // 3. Salary & Commission Me Kitna Dena Hai (All active employees this month)
        $commissionService = app(\App\Services\CommissionService::class);
        $totalSalaryPayableThisMonth = 0;
        $totalCommissionThisMonth = 0;
        $activeEmployees = Employee::active()->get();

        foreach ($activeEmployees as $emp) {
            $existingSal = EmployeeSalary::where('employee_id', $emp->id)
                ->where('month', $now->month)
                ->where('year', $now->year)
                ->first();

            if ($existingSal) {
                $totalSalaryPayableThisMonth += (float) $existingSal->net_payable;
                $totalCommissionThisMonth += (float) $existingSal->total_commission;
            } else {
                $comm = (float) $commissionService->calculateMonthlyCommission($emp, $now->month, $now->year);
                $base = in_array($emp->salary_type, ['fixed', 'both']) ? (float) $emp->fixed_salary : 0;
                $commInSalary = in_array($emp->salary_type, ['package_based', 'both']) ? $comm : 0;
                $pendingAdv = (float) $emp->total_pending_advance;
                $otherDeductions = (float) $emp->salaryDeductions()->where('month', $now->month)->where('year', $now->year)->sum('amount');
                $gross = $base + $commInSalary;
                $advDeduct = min($gross, $pendingAdv);
                $net = max(0, $gross - $advDeduct - $otherDeductions);

                $totalSalaryPayableThisMonth += $net;
                $totalCommissionThisMonth += $comm;
            }
        }

        // 4. Current Month Deductible Expenses (Only included in calculation)
        $monthlyExpenses = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('include_in_calculation', true)
            ->sum('amount');

        $allMonthlyExpenses = Expense::with(['category', 'createdByUser'])
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->orderByDesc('expense_date')
            ->get();

        // 5. Net Projected Savings / Remaining Balance from Receivables
        // Formula: Total Receivables - Total Salary Payable (Base + Commission) - Monthly Expenses
        $netProjectedBachat = $paymentDue - $totalSalaryPayableThisMonth - $monthlyExpenses;

        // 6. Available Cash Fund (Actual Cash in Hand)
        $totalCollection = ClientPayment::sum('amount');
        $totalExpenses = Expense::sum('amount');
        $totalAdvances = \App\Models\EmployeeAdvance::sum('amount');
        $totalSalaryPaid = \App\Models\EmployeeSalary::sum('paid_amount');
        $availableFund = $totalCollection - $totalExpenses - $totalAdvances - $totalSalaryPaid;

        $pendingSalary = EmployeeSalary::where('status', 'pending')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->sum('net_payable');

        // ─── Widgets ────────────────────────────────────────────
        $upcomingRenewals = ClientBillingCycle::with('client')
            ->whereHas('client', function ($q) {
                $q->where('status', 'active');
            })
            ->where('billing_end', '>=', $now)
            ->where('billing_end', '<=', $now->copy()->addDays(7))
            ->where('status', 'pending')
            ->orderBy('billing_end')
            ->limit(10)
            ->get();

        $paymentDueClients = Client::with(['billingCycles' => function ($q) {
                $q->whereIn('status', ['overdue', 'pending', 'partial'])->orderBy('billing_start');
            }])
            ->whereHas('billingCycles', function ($q) {
                $q->whereIn('status', ['overdue', 'pending', 'partial']);
            })
            ->limit(10)
            ->get();

        $recentPayments = ClientPayment::with(['client', 'receivedByUser'])
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $recentExpenses = Expense::with(['category', 'createdByUser'])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $recentActivities = Activity::with('causer')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $followUps = FollowUp::with('client')
            ->pending()
            ->where('follow_up_date', '<=', $now->copy()->addDays(3))
            ->orderBy('follow_up_date')
            ->limit(10)
            ->get();

        // ─── Chart Data ─────────────────────────────────────────
        $monthlyCollectionData = [];
        $monthlyExpenseData = [];
        $monthlyProfitData = [];
        $clientGrowthData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $label = $monthDate->format('M Y');

            $collection = ClientPayment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
            $expense = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');

            $monthlyCollectionData[] = ['label' => $label, 'value' => (float)$collection];
            $monthlyExpenseData[] = ['label' => $label, 'value' => (float)$expense];
            $monthlyProfitData[] = ['label' => $label, 'value' => (float)($collection - $expense)];
            $clientGrowthData[] = ['label' => $label, 'value' => Client::where('created_at', '<=', $monthEnd)->count()];
        }

        $totalClients = Client::count();
        $activeClients = Client::where('status', 'active')->count();
        $inactiveClients = Client::where('status', 'inactive')->count();

        $allClients = Client::active()->with('billingCycles')->get();

        return view('dashboard', compact(
            'totalClients', 'activeClients', 'inactiveClients', 'paymentDue', 'activeDueClientsCount',
            'totalSalaryPayableThisMonth', 'totalCommissionThisMonth', 'monthlyExpenses', 'netProjectedBachat',
            'availableFund', 'todayCollection', 'monthlyCollection', 'pendingSalary',
            'upcomingRenewals', 'paymentDueClients', 'recentPayments', 'recentExpenses',
            'recentActivities', 'followUps', 'allClients', 'allMonthlyExpenses',
            'monthlyCollectionData', 'monthlyExpenseData', 'monthlyProfitData', 'clientGrowthData'
        ));
    }

    public function clearFund(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:all,custom',
            'amount' => 'required_if:mode,custom|nullable|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate available fund
        $totalCollection = \App\Models\ClientPayment::sum('amount');
        $totalExpenses = \App\Models\Expense::sum('amount');
        $totalAdvances = \App\Models\EmployeeAdvance::sum('amount');
        $totalSalaryPaid = \App\Models\EmployeeSalary::sum('paid_amount');
        $availableFund = $totalCollection - $totalExpenses - $totalAdvances - $totalSalaryPaid;

        if ($request->mode === 'all') {
            $amount = $availableFund;
        } else {
            $amount = (float)$request->amount;
        }

        if ($amount <= 0) {
            return back()->withErrors(['amount' => 'Available fund is already zero or negative.']);
        }

        // Find or create "Owner Withdrawal" expense category
        $category = \App\Models\ExpenseCategory::firstOrCreate(
            ['name' => 'Owner Withdrawal'],
            ['description' => 'Funds withdrawn or cleared by owners/partners', 'status' => 'active']
        );

        // Record as an Expense
        \App\Models\Expense::create([
            'title' => 'Fund Cleared by Owner',
            'category_id' => $category->id,
            'amount' => $amount,
            'expense_date' => $request->date,
            'type' => 'one_time',
            'notes' => $request->notes ?? 'Cleared by owner',
            'created_by' => \Auth::id(),
        ]);

        return back()->with('success', '₹' . number_format($amount, 2) . ' fund cleared by owner successfully!');
    }
}
