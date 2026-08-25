<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $month = (int)$request->get('month', now()->month);
        $year = (int)$request->get('year', now()->year);

        $employees = Employee::active()->orderBy('name')->get();
        $existingSalaries = EmployeeSalary::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('employee_id');

        $employeeSalaryList = [];
        $totalMonthlyPayrollDue = 0;
        $totalMonthlyPayrollPaid = 0;

        foreach ($employees as $emp) {
            $existing = $existingSalaries->get($emp->id);
            $deductions = $emp->salaryDeductions()->where('month', $month)->where('year', $year)->get();
            $otherDeductionSum = (float) $deductions->sum('amount');

            if ($existing) {
                $baseSalary = (float) $existing->base_salary;
                $commission = (float) $existing->total_commission;
                $advanceDeduction = (float) $existing->advance_deduction;
                $otherDeductions = (float) ($existing->other_deductions ?: $otherDeductionSum);
                $netPayable = (float) $existing->net_payable;
                $paidAmount = (float) $existing->paid_amount;
                $status = $existing->status;
                $paidDate = $existing->paid_date;
                $salaryId = $existing->id;
            } else {
                $commission = (float) $this->commissionService->calculateMonthlyCommission($emp, $month, $year);
                $baseSalary = in_array($emp->salary_type, ['fixed', 'both']) ? (float) $emp->fixed_salary : 0;
                $commissionInSalary = in_array($emp->salary_type, ['package_based', 'both']) ? $commission : 0;
                
                // Applicable advance deduction
                $pendingAdvanceSum = (float) $emp->total_pending_advance;
                $gross = $baseSalary + $commissionInSalary;
                $advanceDeduction = min($gross, $pendingAdvanceSum);
                $otherDeductions = $otherDeductionSum;
                $netPayable = max(0, $gross - $advanceDeduction - $otherDeductions);
                $paidAmount = 0;
                $status = 'pending';
                $paidDate = null;
                $salaryId = null;
            }

            if ($status === 'paid') {
                $totalMonthlyPayrollPaid += $paidAmount;
            } else {
                $totalMonthlyPayrollDue += $netPayable;
            }

            $employeeSalaryList[] = [
                'employee' => $emp,
                'salary_id' => $salaryId,
                'base_salary' => $baseSalary,
                'commission' => $commission,
                'advance_deduction' => $advanceDeduction,
                'other_deductions' => $otherDeductions,
                'deductions' => $deductions,
                'net_payable' => $netPayable,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'paid_date' => $paidDate,
            ];
        }

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[] = ['month' => $date->month, 'year' => $date->year, 'label' => $date->format('F Y')];
        }

        $pendingAdvanceRequests = \App\Models\EmployeeAdvanceRequest::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingHolidayRequests = \App\Models\EmployeeHolidayRequest::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('salary.index', compact(
            'employeeSalaryList', 'employees', 'months', 'month', 'year',
            'totalMonthlyPayrollDue', 'totalMonthlyPayrollPaid',
            'pendingAdvanceRequests', 'pendingHolidayRequests'
        ));
    }

    public function history(Request $request)
    {
        $query = EmployeeSalary::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaries = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $employees = Employee::orderBy('name')->get();

        $totalPaidSum = EmployeeSalary::where('status', 'paid')->sum('paid_amount');
        $totalCommissionSum = EmployeeSalary::sum('total_commission');
        $totalNetPayableSum = EmployeeSalary::sum('net_payable');

        $years = EmployeeSalary::select('year')->distinct()->orderByDesc('year')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('salary.history', compact('salaries', 'employees', 'years', 'totalPaidSum', 'totalCommissionSum', 'totalNetPayableSum'));
    }

    public function advances()
    {
        $pendingAdvanceRequests = \App\Models\EmployeeAdvanceRequest::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $processedAdvanceRequests = \App\Models\EmployeeAdvanceRequest::with('employee')
            ->where('status', '!=', 'pending')
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        return view('salary.advances', compact('pendingAdvanceRequests', 'processedAdvanceRequests'));
    }

    public function holidays()
    {
        $pendingHolidayRequests = \App\Models\EmployeeHolidayRequest::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $processedHolidayRequests = \App\Models\EmployeeHolidayRequest::with('employee')
            ->where('status', '!=', 'pending')
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        return view('salary.holidays', compact('pendingHolidayRequests', 'processedHolidayRequests'));
    }

    public function approveAdvanceRequest(Request $request, $id)
    {
        $advanceRequest = \App\Models\EmployeeAdvanceRequest::with('employee')->findOrFail($id);
        if ($advanceRequest->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request has already been processed.'], 400);
            }
            return back()->with('error', 'Request has already been processed.');
        }

        DB::transaction(function () use ($advanceRequest) {
            $advanceRequest->update([
                'status' => 'approved',
                'action_by' => Auth::id(),
                'action_at' => now(),
            ]);

            // Create EmployeeAdvance record
            \App\Models\EmployeeAdvance::create([
                'employee_id' => $advanceRequest->employee_id,
                'amount' => $advanceRequest->amount,
                'advance_date' => now()->format('Y-m-d'),
                'deducted' => 0,
                'remaining' => $advanceRequest->amount,
                'status' => 'active',
                'approved_by' => Auth::id(),
                'notes' => 'Approved Advance Request: ' . ($advanceRequest->notes ?? 'No details provided'),
            ]);

            // Notify Employee
            if ($advanceRequest->employee && $advanceRequest->employee->user_id) {
                \App\Models\Notification::create([
                    'user_id' => $advanceRequest->employee->user_id,
                    'type' => 'advance_request_approved',
                    'title' => 'Advance Request Approved',
                    'message' => 'Your advance request of ₹' . number_format($advanceRequest->amount, 2) . ' has been approved and disburse details have been updated in your dashboard.',
                ]);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Advance request approved successfully!']);
        }
        return back()->with('success', 'Advance request approved and advance recorded successfully!');
    }

    public function rejectAdvanceRequest(Request $request, $id)
    {
        $advanceRequest = \App\Models\EmployeeAdvanceRequest::with('employee')->findOrFail($id);
        if ($advanceRequest->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request has already been processed.'], 400);
            }
            return back()->with('error', 'Request has already been processed.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $advanceRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'action_by' => Auth::id(),
            'action_at' => now(),
        ]);

        // Notify Employee with Rejection Reason
        if ($advanceRequest->employee && $advanceRequest->employee->user_id) {
            \App\Models\Notification::create([
                'user_id' => $advanceRequest->employee->user_id,
                'type' => 'advance_request_rejected',
                'title' => 'Advance Request Rejected',
                'message' => 'Your advance request of ₹' . number_format($advanceRequest->amount, 2) . ' was rejected. Reason: ' . $request->reason,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Advance request rejected.']);
        }
        return back()->with('success', 'Advance request rejected successfully!');
    }

    public function approveHolidayRequest(Request $request, $id)
    {
        $holidayRequest = \App\Models\EmployeeHolidayRequest::with('employee')->findOrFail($id);
        if ($holidayRequest->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request has already been processed.'], 400);
            }
            return back()->with('error', 'Request has already been processed.');
        }

        $holidayRequest->update([
            'status' => 'approved',
            'action_by' => Auth::id(),
            'action_at' => now(),
        ]);

        // Notify Employee
        if ($holidayRequest->employee && $holidayRequest->employee->user_id) {
            \App\Models\Notification::create([
                'user_id' => $holidayRequest->employee->user_id,
                'type' => 'holiday_request_approved',
                'title' => 'Holiday Request Approved',
                'message' => 'Your holiday request from ' . $holidayRequest->start_date->format('d/m/Y') . ' to ' . $holidayRequest->end_date->format('d/m/Y') . ' has been approved.',
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Holiday request approved successfully!']);
        }
        return back()->with('success', 'Holiday request approved successfully!');
    }

    public function rejectHolidayRequest(Request $request, $id)
    {
        $holidayRequest = \App\Models\EmployeeHolidayRequest::with('employee')->findOrFail($id);
        if ($holidayRequest->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request has already been processed.'], 400);
            }
            return back()->with('error', 'Request has already been processed.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $holidayRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'action_by' => Auth::id(),
            'action_at' => now(),
        ]);

        // Notify Employee with Rejection Reason
        if ($holidayRequest->employee && $holidayRequest->employee->user_id) {
            \App\Models\Notification::create([
                'user_id' => $holidayRequest->employee->user_id,
                'type' => 'holiday_request_rejected',
                'title' => 'Holiday Request Rejected',
                'message' => 'Your holiday request from ' . $holidayRequest->start_date->format('d/m/Y') . ' to ' . $holidayRequest->end_date->format('d/m/Y') . ' was rejected. Reason: ' . $request->reason,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Holiday request rejected.']);
        }
        return back()->with('success', 'Holiday request rejected successfully!');
    }

    public function generate(Request $request)
    {
        if ($request->filled('month_year')) {
            $parts = explode('-', $request->month_year);
            if (count($parts) === 2) {
                $request->merge([
                    'month' => (int)$parts[0],
                    'year' => (int)$parts[1],
                ]);
            }
        }

        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        if ($request->filled('employee_id')) {
            $employee = Employee::findOrFail($request->employee_id);
            $existing = EmployeeSalary::where('employee_id', $employee->id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();
            if ($existing && $existing->status === 'paid') {
                return redirect()->back()->with('error', "Salary for {$employee->name} is already paid for this period.");
            }
            $this->commissionService->generateSalary($employee, $request->month, $request->year);
            $msg = "Salary generated for {$employee->name}";
        } else {
            // Generate for all active employees
            $employees = Employee::active()->get();
            $count = 0;
            foreach ($employees as $employee) {
                $existing = EmployeeSalary::where('employee_id', $employee->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->first();
                if ($existing && $existing->status === 'paid') {
                    continue;
                }
                $this->commissionService->generateSalary($employee, $request->month, $request->year);
                $count++;
            }
            $msg = "Salary generated for {$count} active employees (already paid skipped)";
        }

        return redirect()->route('salary.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', $msg);
    }

    public function pay(Request $request, EmployeeSalary $salary)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($salary->net_payable - $salary->paid_amount),
        ]);

        $this->commissionService->paySalary($salary, $request->amount);

        return redirect()->route('salary.index', ['month' => $salary->month, 'year' => $salary->year])
            ->with('success', '₹' . number_format($request->amount, 2) . ' salary paid to ' . $salary->employee->name);
    }

    public function advanceForm()
    {
        $employees = Employee::active()->get();
        return view('salary.advance', compact('employees'));
    }

    public function processAdvance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'advance_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $this->commissionService->processAdvance($employee, $request->amount, $request->notes ?? '', null, $request->advance_date);

        return redirect()->route('employees.show', $employee)
            ->with('success', '₹' . number_format($request->amount, 2) . ' advance processed for ' . $employee->name);
    }

    public function previewSalary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $month = (int)$request->month;
        $year = (int)$request->year;

        $existing = EmployeeSalary::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->status === 'paid') {
            return response()->json(['error' => 'already_paid', 'message' => 'Salary is already paid for this period.'], 422);
        }

        $totalCommission = $this->commissionService->calculateMonthlyCommission($employee, $month, $year);
        $baseSalary = in_array($employee->salary_type, ['fixed', 'both']) ? $employee->fixed_salary : 0;
        $commissionInSalary = in_array($employee->salary_type, ['package_based', 'both']) ? $totalCommission : 0;

        // Calculate advance deductions
        $advanceDeduction = 0;
        $pendingAdvances = $employee->pendingAdvances()
            ->where(function($query) use ($month, $year) {
                $query->whereYear('advance_date', '<', $year)
                      ->orWhere(function($q) use ($month, $year) {
                          $q->whereYear('advance_date', $year)
                            ->whereMonth('advance_date', '<=', $month);
                      });
            })
            ->orderBy('advance_date')
            ->get();
        $remainingForDeduction = $baseSalary + $commissionInSalary;

        foreach ($pendingAdvances as $advance) {
            if ($remainingForDeduction <= 0) break;
            $deductAmount = min($advance->remaining, $remainingForDeduction);
            $advanceDeduction += $deductAmount;
            $remainingForDeduction -= $deductAmount;
        }

        $netPayable = max(0, $baseSalary + $commissionInSalary - $advanceDeduction);

        return response()->json([
            'base_salary' => $baseSalary,
            'total_commission' => $commissionInSalary,
            'advance_deduction' => $advanceDeduction,
            'net_payable' => $netPayable,
        ]);
    }

    public function generateAndPay(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'payment_mode' => 'required|in:calculated,custom',
            'custom_amount' => 'required_if:payment_mode,custom|nullable|numeric|min:0',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $month = (int)$request->month;
        $year = (int)$request->year;

        $existing = EmployeeSalary::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->status === 'paid') {
            return redirect()->back()->with('error', "Salary is already paid for this period.");
        }

        // Perform calculation
        $totalCommission = $this->commissionService->calculateMonthlyCommission($employee, $month, $year);
        $baseSalary = in_array($employee->salary_type, ['fixed', 'both']) ? $employee->fixed_salary : 0;
        $commissionInSalary = in_array($employee->salary_type, ['package_based', 'both']) ? $totalCommission : 0;

        if ($request->payment_mode === 'calculated') {
            // Deduct advance
            $advanceDeduction = 0;
            $pendingAdvances = $employee->pendingAdvances()
                ->where(function($query) use ($month, $year) {
                    $query->whereYear('advance_date', '<', $year)
                          ->orWhere(function($q) use ($month, $year) {
                              $q->whereYear('advance_date', $year)
                                ->whereMonth('advance_date', '<=', $month);
                          });
                })
                ->orderBy('advance_date')
                ->get();
            $remainingForDeduction = $baseSalary + $commissionInSalary;

            foreach ($pendingAdvances as $advance) {
                if ($remainingForDeduction <= 0) break;
                $deductAmount = min($advance->remaining, $remainingForDeduction);
                $advanceDeduction += $deductAmount;
                $remainingForDeduction -= $deductAmount;

                $advance->deducted += $deductAmount;
                $advance->remaining -= $deductAmount;
                $advance->status = $advance->remaining <= 0 ? 'fully_deducted' : 'partially_deducted';
                $advance->save();
            }

            $otherDeductions = (float) $employee->salaryDeductions()->where('month', $month)->where('year', $year)->sum('amount');
            $netPayable = max(0, $baseSalary + $commissionInSalary - $advanceDeduction - $otherDeductions);
            $paidAmount = $netPayable;
        } else {
            // Custom amount payment
            $otherDeductions = (float) $employee->salaryDeductions()->where('month', $month)->where('year', $year)->sum('amount');
            $netPayable = (float)$request->custom_amount;
            $paidAmount = $netPayable;
            $advanceDeduction = 0; // custom payout doesn't automatically deduct advance unless specified
        }

        $salary = EmployeeSalary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'base_salary' => $baseSalary,
                'total_commission' => $commissionInSalary,
                'advance_deduction' => $advanceDeduction,
                'other_deductions' => $otherDeductions,
                'bonus' => 0,
                'net_payable' => $netPayable,
                'paid_amount' => $paidAmount,
                'status' => 'paid',
                'paid_date' => now(),
            ]
        );

        // Mark all pending commissions for this month as paid
        \App\Models\EmployeeCommission::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_date' => now()]);

        return redirect()->route('salary.index', ['month' => $month, 'year' => $year])
            ->with('success', "Salary of ₹" . number_format($paidAmount, 2) . " generated and paid for {$employee->name}");
    }

    public function payQuick(Request $request, Employee $employee)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        $salary = EmployeeSalary::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$salary) {
            $salary = $this->commissionService->generateSalary($employee, $month, $year);
        }

        if ($salary->status !== 'paid') {
            $this->commissionService->paySalary($salary, (float) $salary->net_payable);
        }

        return redirect()->back()->with('success', 'Salary of ₹' . number_format($salary->net_payable, 2) . ' marked as PAID for ' . $employee->name . ' successfully!');
    }

    public function storeDeduction(Request $request, Employee $employee)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:500',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;
        $amount = (float) $request->amount;
        $reason = $request->reason;

        $deduction = \App\Models\EmployeeSalaryDeduction::create([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'amount' => $amount,
            'reason' => $reason,
            'created_by' => Auth::id(),
        ]);

        // Update pending salary if draft exists
        $existing = EmployeeSalary::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->status !== 'paid') {
            $otherDeductionSum = (float) $employee->salaryDeductions()->where('month', $month)->where('year', $year)->sum('amount');
            $existing->other_deductions = $otherDeductionSum;
            $existing->net_payable = max(0, (float)$existing->base_salary + (float)$existing->total_commission - (float)$existing->advance_deduction - $otherDeductionSum);
            $existing->save();
        }

        // Notify Employee on Dashboard
        if ($employee->user_id) {
            \App\Models\Notification::create([
                'user_id' => $employee->user_id,
                'type' => 'salary_deduction',
                'title' => 'Salary Deduction: ₹' . number_format($amount, 0),
                'message' => 'An amount of ₹' . number_format($amount, 2) . ' has been deducted from your ' . Carbon::create($year, $month, 1)->format('F Y') . ' salary. Reason: ' . $reason,
                'data' => [
                    'amount' => $amount,
                    'reason' => $reason,
                    'month' => $month,
                    'year' => $year,
                ],
            ]);
        }

        return redirect()->back()->with('success', '₹' . number_format($amount, 2) . ' deducted from ' . $employee->name . '\'s salary for ' . Carbon::create($year, $month, 1)->format('F Y'));
    }

    public function destroyDeduction(\App\Models\EmployeeSalaryDeduction $deduction)
    {
        $employee = $deduction->employee;
        $amount = (float) $deduction->amount;
        $month = $deduction->month;
        $year = $deduction->year;

        $deduction->delete();

        // Update pending salary if draft exists
        if ($employee) {
            $existing = EmployeeSalary::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing && $existing->status !== 'paid') {
                $otherDeductionSum = (float) $employee->salaryDeductions()->where('month', $month)->where('year', $year)->sum('amount');
                $existing->other_deductions = $otherDeductionSum;
                $existing->net_payable = max(0, (float)$existing->base_salary + (float)$existing->total_commission - (float)$existing->advance_deduction - $otherDeductionSum);
                $existing->save();
            }

            // Notify Employee on Dashboard
            if ($employee->user_id) {
                \App\Models\Notification::create([
                    'user_id' => $employee->user_id,
                    'type' => 'salary_deduction_reverted',
                    'title' => 'Salary Deduction Cancelled (+₹' . number_format($amount, 0) . ')',
                    'message' => 'The salary deduction of ₹' . number_format($amount, 2) . ' for ' . Carbon::create($year, $month, 1)->format('F Y') . ' has been cancelled and refunded to your salary.',
                    'data' => [
                        'amount' => $amount,
                        'month' => $month,
                        'year' => $year,
                    ],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Salary deduction of ₹' . number_format($amount, 2) . ' cancelled and refunded to salary.');
    }
}
