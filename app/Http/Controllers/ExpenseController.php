<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $timeFrame = $request->get('time_frame', 'month'); // 'month', 'all', 'custom'
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $activeTab = $request->get('tab', 'all'); // 'all', 'general', 'salaries', 'advances'

        $now = \Carbon\Carbon::create($year, $month, 1);
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. General Expenses Query
        $expensesQuery = Expense::with(['category', 'createdByUser']);
        $salariesQuery = \App\Models\EmployeeSalary::with('employee')->where('status', 'paid');
        $advancesQuery = \App\Models\EmployeeAdvance::with('employee');

        if ($timeFrame === 'month') {
            $expensesQuery->whereBetween('expense_date', [$startOfMonth, $endOfMonth]);
            $salariesQuery->where(function($q) use ($startOfMonth, $endOfMonth, $month, $year) {
                $q->whereBetween('paid_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function($sub) use ($month, $year) {
                      $sub->where('month', $month)->where('year', $year);
                  });
            });
            $advancesQuery->whereBetween('advance_date', [$startOfMonth, $endOfMonth]);
        } elseif ($timeFrame === 'custom') {
            if ($request->filled('date_from')) {
                $expensesQuery->where('expense_date', '>=', $request->date_from);
                $salariesQuery->where('paid_date', '>=', $request->date_from);
                $advancesQuery->where('advance_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $expensesQuery->where('expense_date', '<=', $request->date_to);
                $salariesQuery->where('paid_date', '<=', $request->date_to);
                $advancesQuery->where('advance_date', '<=', $request->date_to);
            }
        }

        // Search & Filters on General Expenses
        if ($request->filled('search')) {
            $expensesQuery->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $expensesQuery->where('category_id', $request->category_id);
        }
        if ($request->filled('type')) {
            $expensesQuery->where('type', $request->type);
        }

        // Totals
        $totalGeneralExpenses = (float) (clone $expensesQuery)->sum('amount');
        $totalPaidSalaries = (float) (clone $salariesQuery)->sum('paid_amount');
        $totalAdvances = (float) (clone $advancesQuery)->sum('amount');
        $totalCombinedExpenses = $totalGeneralExpenses + $totalPaidSalaries + $totalAdvances;

        $expenses = $expensesQuery->orderByDesc('expense_date')->paginate(20, ['*'], 'expenses_page')->appends($request->query());
        $paidSalaries = $salariesQuery->orderByDesc('paid_date')->paginate(20, ['*'], 'salaries_page')->appends($request->query());
        $advances = $advancesQuery->orderByDesc('advance_date')->paginate(20, ['*'], 'advances_page')->appends($request->query());

        $categories = ExpenseCategory::active()->get();

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $months[] = ['month' => $date->month, 'year' => $date->year, 'label' => $date->format('F Y')];
        }

        return view('expenses.index', compact(
            'expenses', 'paidSalaries', 'advances', 'categories',
            'totalGeneralExpenses', 'totalPaidSalaries', 'totalAdvances', 'totalCombinedExpenses',
            'timeFrame', 'month', 'year', 'months', 'activeTab'
        ));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'type' => 'required|in:monthly,one_time',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120',
        ]);

        $data = $request->only(['title', 'category_id', 'amount', 'expense_date', 'type', 'notes']);
        $data['created_by'] = Auth::id();

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('receipts', 'public');
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully!');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'type' => 'required|in:monthly,one_time',
            'notes' => 'nullable|string',
        ]);

        $expense->update($request->only(['title', 'category_id', 'amount', 'expense_date', 'type', 'notes']));

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully!');
    }

    public function toggleCalculation(Request $request, Expense $expense)
    {
        $expense->include_in_calculation = !$expense->include_in_calculation;
        $expense->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'include_in_calculation' => $expense->include_in_calculation,
                'message' => $expense->include_in_calculation 
                    ? 'Expense included in monthly deductions.' 
                    : 'Expense excluded from monthly deductions (deferred to future).',
            ]);
        }

        return redirect()->back()->with('success', 'Expense deduction status updated.');
    }
}
