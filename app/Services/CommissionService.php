<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeSalary;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeClientAssignment;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate commission for an employee for a specific month/year
     */
    public function calculateMonthlyCommission(Employee $employee, int $month, int $year): float
    {
        $totalCommission = 0;

        // Get all active client assignments
        $assignments = $employee->activeAssignments()->with('client')->get();

        foreach ($assignments as $assignment) {
            $client = $assignment->client;
            if (!$client || $client->status !== 'active') continue;

            $commission = $this->calculateCommissionForClient($employee, $client, $month, $year);
            $totalCommission += $commission;
        }

        return $totalCommission;
    }

    /**
     * Calculate commission for a specific employee-client pair
     */
    public function calculateCommissionForClient(Employee $employee, Client $client, int $month, int $year): float
    {
        $amount = 0;

        // Check if there is an active custom assignment commission setting
        $assignment = EmployeeClientAssignment::where('employee_id', $employee->id)
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->first();

        if ($assignment && $assignment->commission_type !== null) {
            $commissionType = $assignment->commission_type;
            $commissionValue = $assignment->commission_value;
            $baseAmount = $assignment->custom_package_amount ?? $client->current_package;
        } else {
            $commissionType = $employee->commission_type;
            $commissionValue = $employee->commission_value;
            $baseAmount = $client->current_package;
        }

        if ($commissionType === 'fixed_amount') {
            $amount = $commissionValue;
        } elseif ($commissionType === 'percentage') {
            $amount = ($baseAmount * $commissionValue) / 100;
        }

        // Create or update commission record
        EmployeeCommission::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'package_amount' => $client->current_package,
                'commission_type' => $commissionType,
                'commission_value' => $commissionValue,
                'calculated_amount' => $amount,
            ]
        );

        return $amount;
    }

    /**
     * Recalculate all commissions for an employee when a client's package changes
     */
    public function recalculateForPackageChange(Client $client): void
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        // Find all employees assigned to this client
        $assignments = EmployeeClientAssignment::where('client_id', $client->id)
            ->where('status', 'active')
            ->with('employee')
            ->get();

        foreach ($assignments as $assignment) {
            $this->calculateCommissionForClient($assignment->employee, $client, $month, $year);
        }
    }

    /**
     * Generate monthly salary for an employee
     */
    public function generateSalary(Employee $employee, int $month, int $year): EmployeeSalary
    {
        return DB::transaction(function () use ($employee, $month, $year) {
            $existing = EmployeeSalary::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing && $existing->status === 'paid') {
                return $existing;
            }

            // Calculate total commission for the month
            $totalCommission = $this->calculateMonthlyCommission($employee, $month, $year);

            // Calculate base salary
            $baseSalary = 0;
            if (in_array($employee->salary_type, ['fixed', 'both'])) {
                $baseSalary = $employee->fixed_salary;
            }

            // Include commission in salary if type is package_based or both
            $commissionInSalary = 0;
            if (in_array($employee->salary_type, ['package_based', 'both'])) {
                $commissionInSalary = $totalCommission;
            }

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
            $remainingForDeduction = $baseSalary + $commissionInSalary; // Max deduction is total salary

            foreach ($pendingAdvances as $advance) {
                if ($remainingForDeduction <= 0) break;

                $deductAmount = min($advance->remaining, $remainingForDeduction); // Deduct up to 100% of remaining salary
                $advanceDeduction += $deductAmount;
                $remainingForDeduction -= $deductAmount;

                // Update advance
                $advance->deducted += $deductAmount;
                $advance->remaining -= $deductAmount;
                $advance->status = $advance->remaining <= 0 ? 'fully_deducted' : 'partially_deducted';
                $advance->save();
            }

            $otherDeductions = (float) $employee->salaryDeductions()->where('month', $month)->where('year', $year)->sum('amount');
            $netPayable = max(0, $baseSalary + $commissionInSalary - $advanceDeduction - $otherDeductions);

            return EmployeeSalary::updateOrCreate(
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
                    'status' => 'pending',
                ]
            );
        });
    }

    /**
     * Pay salary (full or partial)
     */
    public function paySalary(EmployeeSalary $salary, float $amount): EmployeeSalary
    {
        return DB::transaction(function () use ($salary, $amount) {
            $salary->paid_amount += $amount;
            $salary->paid_date = now();

            if ($salary->paid_amount >= $salary->net_payable) {
                $salary->status = 'paid';
                // Mark all pending commissions for this month as paid
                EmployeeCommission::where('employee_id', $salary->employee_id)
                    ->where('month', $salary->month)
                    ->where('year', $salary->year)
                    ->where('status', 'pending')
                    ->update(['status' => 'paid', 'paid_date' => now()]);
            } else {
                $salary->status = 'partial';
            }

            $salary->save();
            return $salary;
        });
    }

    /**
     * Process salary advance
     */
    public function processAdvance(Employee $employee, float $amount, string $notes = '', int $approvedBy = null, $advanceDate = null): EmployeeAdvance
    {
        return EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'amount' => $amount,
            'advance_date' => $advanceDate ? Carbon::parse($advanceDate) : now(),
            'deducted' => 0,
            'remaining' => $amount,
            'notes' => $notes,
            'approved_by' => $approvedBy ?? auth()->id(),
            'status' => 'active',
        ]);
    }
}
