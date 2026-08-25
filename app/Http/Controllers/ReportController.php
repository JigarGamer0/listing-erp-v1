<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientPayment;
use App\Models\ClientBillingCycle;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeCommission;
use App\Models\EmployeeAdvance;
use App\Models\Investment;
use App\Models\Investor;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function collection(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $payments = ClientPayment::with(['client', 'receivedByUser'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderByDesc('payment_date')
            ->get();

        $totalCollection = $payments->sum('amount');

        // Group by client
        $clientWise = $payments->groupBy('client_id')->map(function ($group) {
            return [
                'client' => $group->first()->client,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        });

        return view('reports.collection', compact('payments', 'totalCollection', 'clientWise', 'dateFrom', 'dateTo'));
    }

    public function expense(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $expenses = $this->compileExpenses($dateFrom, $dateTo);
        $totalExpense = $expenses->sum('amount');

        $categoryWise = $expenses->groupBy(function ($item) {
            return $item->category->name;
        })->map(function ($group, $catName) {
            return [
                'category' => (object)['name' => $catName],
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        });

        return view('reports.expense', compact('expenses', 'totalExpense', 'categoryWise', 'dateFrom', 'dateTo'));
    }

    public function profit(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $monthlyData = [];
        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        while ($start->lte($end)) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();

            $collection = ClientPayment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
            
            $generalExpense = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');
            $salaryPaid = EmployeeSalary::where('status', 'paid')->whereBetween('paid_date', [$monthStart, $monthEnd])->sum('paid_amount');
            $advanceGiven = EmployeeAdvance::whereBetween('advance_date', [$monthStart, $monthEnd])->sum('amount');
            $expense = $generalExpense + $salaryPaid + $advanceGiven;

            $monthlyData[] = [
                'month' => $start->format('M Y'),
                'collection' => (float)$collection,
                'expense' => (float)$expense,
                'profit' => (float)($collection - $expense),
            ];

            $start->addMonth();
        }

        $totalCollection = array_sum(array_column($monthlyData, 'collection'));
        $totalExpense = array_sum(array_column($monthlyData, 'expense'));
        $totalProfit = $totalCollection - $totalExpense;

        return view('reports.profit', compact('monthlyData', 'totalCollection', 'totalExpense', 'totalProfit', 'dateFrom', 'dateTo'));
    }

    public function clientGrowth(Request $request)
    {
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthEnd = $date->copy()->endOfMonth();
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'total' => Client::where('created_at', '<=', $monthEnd)->count(),
                'new' => Client::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
            ];
        }

        return view('reports.client-growth', compact('monthlyData'));
    }

    public function salary(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $salaries = EmployeeSalary::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('reports.salary', compact('salaries', 'month', 'year'));
    }

    public function commission(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $commissions = EmployeeCommission::with(['employee', 'client'])
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $grouped = $commissions->groupBy('employee_id');

        return view('reports.commission', compact('commissions', 'grouped', 'month', 'year'));
    }

    public function pendingPayments(Request $request)
    {
        $clients = Client::with(['billingCycles' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue'])->orderBy('billing_start');
            }])
            ->whereHas('billingCycles', function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            })
            ->get()
            ->map(function ($client) {
                $client->total_pending = $client->billingCycles->sum('balance');
                $client->pending_months = $client->billingCycles->count();
                return $client;
            })
            ->sortByDesc('total_pending');

        $totalPending = $clients->sum('total_pending');

        return view('reports.pending-payments', compact('clients', 'totalPending'));
    }

    public function fullReport(Request $request)
    {
        $dateFrom = $request->get('date_from', null);
        $dateTo = $request->get('date_to', null);
        $filterByDate = $dateFrom && $dateTo;

        // ═══════════════════════════════════════════════════════
        // INCOME SIDE: All money that came in
        // ═══════════════════════════════════════════════════════
        $paymentsQuery = ClientPayment::with('client');
        if ($filterByDate) {
            $paymentsQuery->whereBetween('payment_date', [$dateFrom, $dateTo]);
        }
        $allPayments = $paymentsQuery->orderByDesc('payment_date')->get();
        $totalCollection = $allPayments->sum('amount');

        // Client-wise collection breakdown
        $clientWiseCollection = $allPayments->groupBy('client_id')->map(function ($group) {
            return [
                'client' => $group->first()->client,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'payments' => $group,
            ];
        })->sortByDesc('total');

        // ═══════════════════════════════════════════════════════
        // EXPENSE SIDE: All money that went out
        // ═══════════════════════════════════════════════════════
        $expensesQuery = Expense::with(['category', 'createdByUser']);
        if ($filterByDate) {
            $expensesQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
        }
        $allExpenses = $expensesQuery->orderByDesc('expense_date')->get();
        $totalExpenses = $allExpenses->sum('amount');

        // Category-wise expense breakdown
        $categoryWiseExpense = $allExpenses->groupBy(function ($e) {
            return $e->category->name ?? 'Uncategorized';
        })->map(function ($group, $catName) {
            return [
                'category' => $catName,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('total');

        // ═══════════════════════════════════════════════════════
        // SALARY SIDE: Paid salaries
        // ═══════════════════════════════════════════════════════
        $salariesQuery = EmployeeSalary::with('employee');
        if ($filterByDate) {
            $salariesQuery->whereHas('employee', function ($q) {});
            // Filter by paid_at or by month/year range
            $from = Carbon::parse($dateFrom);
            $to = Carbon::parse($dateTo);
            $salariesQuery->where(function ($q) use ($from, $to) {
                $q->where(function ($q2) use ($from) {
                    $q2->where('year', '>', $from->year)
                       ->orWhere(function ($q3) use ($from) {
                           $q3->where('year', $from->year)->where('month', '>=', $from->month);
                       });
                })->where(function ($q2) use ($to) {
                    $q2->where('year', '<', $to->year)
                       ->orWhere(function ($q3) use ($to) {
                           $q3->where('year', $to->year)->where('month', '<=', $to->month);
                       });
                });
            });
        }
        $allSalaries = $salariesQuery->get();
        $totalSalaryPaid = $allSalaries->where('status', 'paid')->sum('paid_amount');

        // Employee-wise salary breakdown
        $employeeWiseSalary = $allSalaries->groupBy('employee_id')->map(function ($group) {
            return [
                'employee' => $group->first()->employee,
                'total_paid' => $group->where('status', 'paid')->sum('paid_amount'),
                'total_pending' => $group->where('status', 'pending')->sum('net_payable'),
                'records' => $group,
            ];
        })->sortByDesc('total_paid');

        // ═══════════════════════════════════════════════════════
        // ADVANCE SIDE: Employee advances given
        // ═══════════════════════════════════════════════════════
        $advancesQuery = EmployeeAdvance::with('employee');
        if ($filterByDate) {
            $advancesQuery->whereBetween('advance_date', [$dateFrom, $dateTo]);
        }
        $allAdvances = $advancesQuery->get();
        $totalAdvances = $allAdvances->sum('amount');
        $totalAdvancesDeducted = $allAdvances->sum('deducted');
        $totalAdvancesRemaining = $allAdvances->sum('remaining');

        // Employee-wise advance breakdown
        $employeeWiseAdvance = $allAdvances->groupBy('employee_id')->map(function ($group) {
            return [
                'employee' => $group->first()->employee,
                'total_given' => $group->sum('amount'),
                'total_deducted' => $group->sum('deducted'),
                'total_remaining' => $group->sum('remaining'),
            ];
        })->sortByDesc('total_given');

        // ═══════════════════════════════════════════════════════
        // INVESTOR SIDE: Who invested and what's cleared
        // ═══════════════════════════════════════════════════════
        $investors = Investor::with(['investments'])->get();
        $investorSummary = $investors->map(function ($inv) use ($filterByDate, $dateFrom, $dateTo) {
            $investments = $inv->investments;
            if ($filterByDate) {
                $investments = $investments->filter(function ($i) use ($dateFrom, $dateTo) {
                    return $i->investment_date >= $dateFrom && $i->investment_date <= $dateTo;
                });
            }
            return [
                'investor' => $inv,
                'total_invested' => $investments->sum('amount'),
                'total_cleared' => $investments->where('status', 'cleared')->sum('amount'),
                'total_uncleared' => $investments->where('status', 'uncleared')->sum('amount'),
                'entries_count' => $investments->count(),
            ];
        })->filter(fn($i) => $i['entries_count'] > 0)->sortByDesc('total_invested');

        $totalInvested = $investorSummary->sum('total_invested');
        $totalInvestmentCleared = $investorSummary->sum('total_cleared');
        $totalInvestmentUncleared = $investorSummary->sum('total_uncleared');

        // ═══════════════════════════════════════════════════════
        // OUTSTANDING DUES: Client ke kitne baki hai
        // ═══════════════════════════════════════════════════════
        $clientDues = Client::with(['billingCycles' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            }])
            ->whereHas('billingCycles', function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            })
            ->get()
            ->map(function ($c) {
                $c->total_pending_amount = $c->billingCycles->sum('balance');
                return $c;
            })
            ->sortByDesc('total_pending_amount');
        $totalOutstanding = $clientDues->sum('total_pending_amount');

        // ═══════════════════════════════════════════════════════
        // FUND CALCULATION: Final hisab
        // ═══════════════════════════════════════════════════════
        // All-time fund (not affected by date filter)
        $allTimeCollection = ClientPayment::sum('amount');
        $allTimeExpenses = Expense::sum('amount');
        $allTimeAdvances = EmployeeAdvance::sum('amount');
        $allTimeSalaryPaid = EmployeeSalary::where('status', 'paid')->sum('paid_amount');
        $availableFund = $allTimeCollection - $allTimeExpenses - $allTimeAdvances - $allTimeSalaryPaid;

        return view('reports.full-report', compact(
            'dateFrom', 'dateTo', 'filterByDate',
            'totalCollection', 'clientWiseCollection', 'allPayments',
            'totalExpenses', 'categoryWiseExpense', 'allExpenses',
            'totalSalaryPaid', 'employeeWiseSalary', 'allSalaries',
            'totalAdvances', 'totalAdvancesDeducted', 'totalAdvancesRemaining',
            'employeeWiseAdvance', 'allAdvances',
            'investorSummary', 'totalInvested', 'totalInvestmentCleared', 'totalInvestmentUncleared',
            'clientDues', 'totalOutstanding',
            'allTimeCollection', 'allTimeExpenses', 'allTimeAdvances', 'allTimeSalaryPaid', 'availableFund'
        ));
    }

    public function export(Request $request, $type)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        switch ($type) {
            case 'collection':
                $data = ClientPayment::with('client')
                    ->whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->orderByDesc('payment_date')
                    ->get();
                $pdf = Pdf::loadView('reports.pdf.collection', compact('data', 'dateFrom', 'dateTo'));
                return $pdf->download('collection_report_' . $dateFrom . '_to_' . $dateTo . '.pdf');

            case 'expense':
                $data = $this->compileExpenses($dateFrom, $dateTo);
                $pdf = Pdf::loadView('reports.pdf.expense', compact('data', 'dateFrom', 'dateTo'));
                return $pdf->download('expense_report_' . $dateFrom . '_to_' . $dateTo . '.pdf');

            case 'pending-payments':
                $data = Client::with(['billingCycles' => function ($q) {
                        $q->whereIn('status', ['pending', 'partial', 'overdue']);
                    }])
                    ->whereHas('billingCycles', function ($q) {
                        $q->whereIn('status', ['pending', 'partial', 'overdue']);
                    })
                    ->get();
                $pdf = Pdf::loadView('reports.pdf.pending-payments', compact('data'));
                return $pdf->download('pending_payments_report.pdf');

            default:
                return back()->with('error', 'Invalid report type.');
        }
    }

    private function compileExpenses($dateFrom, $dateTo)
    {
        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($e) {
                return (object)[
                    'id' => $e->id,
                    'expense_date' => $e->expense_date,
                    'title' => $e->title,
                    'category' => (object)['name' => $e->category->name ?? 'Uncategorized'],
                    'amount' => (float)$e->amount,
                ];
            });

        // Add Salaries
        $salaries = EmployeeSalary::with('employee')
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($s) {
                return (object)[
                    'id' => 'sal_' . $s->id,
                    'expense_date' => Carbon::parse($s->paid_date),
                    'title' => 'Salary Paid - ' . ($s->employee->name ?? 'Unknown') . ' (' . Carbon::create(null, $s->month)->format('F') . ' ' . $s->year . ')',
                    'category' => (object)['name' => 'Employee Salary'],
                    'amount' => (float)$s->paid_amount,
                ];
            });

        // Add Advances
        $advances = EmployeeAdvance::with('employee')
            ->whereBetween('advance_date', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($a) {
                return (object)[
                    'id' => 'adv_' . $a->id,
                    'expense_date' => $a->advance_date,
                    'title' => 'Advance Given - ' . ($a->employee->name ?? 'Unknown'),
                    'category' => (object)['name' => 'Employee Advance'],
                    'amount' => (float)$a->amount,
                ];
            });

        return $expenses->concat($salaries)->concat($advances)->sortByDesc('expense_date');
    }
}
