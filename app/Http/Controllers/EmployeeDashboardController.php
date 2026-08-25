<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeAdvanceRequest;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeHolidayRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    private function getEmployeeOrAbort()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not associated with this user account.');
        }
        return $employee;
    }

    private function getExpectedCommissionAndClients($employee)
    {
        $assignments = $employee->activeAssignments()->with('client')->get();
        $expectedCommission = 0;
        $clientDetails = [];

        foreach ($assignments as $assign) {
            $client = $assign->client;
            if (!$client) continue;

            $pkg = $assign->custom_package_amount ?: $client->current_package;
            $commission = 0;

            if ($assign->commission_type === 'percentage') {
                $commission = ($pkg * $assign->commission_value) / 100;
            } else {
                $commission = $assign->commission_value;
            }

            $expectedCommission += $commission;

            $flipkartGst = 0;
            $meeshoGst = 0;
            if ($assign->gst_platform === 'flipkart') {
                $flipkartGst = $assign->gst_count;
            } elseif ($assign->gst_platform === 'meesho') {
                $meeshoGst = $assign->gst_count;
            }

            $clientDetails[] = [
                'client_id' => $client->id,
                'name' => $client->name,
                'work_location' => $client->work_location,
                'flipkart_gst' => $flipkartGst,
                'meesho_gst' => $meeshoGst,
                'platform' => $assign->gst_platform,
                'gst_count' => $assign->gst_count,
                'commission_type' => $assign->commission_type,
                'commission_value' => $assign->commission_value,
                'commission_amount' => $commission,
                'package_amount' => $pkg,
            ];
        }

        return [$expectedCommission, $clientDetails];
    }

    public function index()
    {
        $employee = $this->getEmployeeOrAbort();
        list($expectedCommission, $clientDetails) = $this->getExpectedCommissionAndClients($employee);
        
        $totalDeducted = $employee->advances()->sum('deducted');
        $pendingAdvanceBalance = $employee->total_pending_advance;

        $grossEarnings = 0;
        if ($employee->salary_type === 'salary') {
            $grossEarnings = $employee->fixed_salary;
        } elseif ($employee->salary_type === 'commission') {
            $grossEarnings = $expectedCommission;
        } else {
            $grossEarnings = $employee->fixed_salary + $expectedCommission;
        }
        $netExpectedPayout = max(0, $grossEarnings - $pendingAdvanceBalance);

        return view('employee.dashboard', compact(
            'employee', 'clientDetails', 'expectedCommission', 'totalDeducted', 'pendingAdvanceBalance', 'netExpectedPayout', 'grossEarnings'
        ));
    }

    public function clients()
    {
        $employee = $this->getEmployeeOrAbort();
        list($expectedCommission, $clientDetails) = $this->getExpectedCommissionAndClients($employee);

        // Fetch today's work logs for this employee
        $todayLogs = \App\Models\EmployeeDailyWorkLog::where('employee_id', $employee->id)
            ->where('log_date', now()->format('Y-m-d'))
            ->get()
            ->keyBy('client_id');

        return view('employee.clients', compact('employee', 'clientDetails', 'todayLogs'));
    }

    public function storeDailyWorkLog(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'listings_count' => 'required|integer|min:0',
            'is_done' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $employee = $this->getEmployeeOrAbort();

        // Check if the client is assigned to this employee
        $isAssigned = $employee->activeAssignments()->where('client_id', $request->client_id)->exists();
        if (!$isAssigned) {
            return back()->with('error', 'This client is not assigned to you.');
        }

        \App\Models\EmployeeDailyWorkLog::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'client_id' => $request->client_id,
                'log_date' => now()->format('Y-m-d'),
            ],
            [
                'listings_count' => $request->listings_count,
                'is_done' => $request->has('is_done') ? (bool)$request->is_done : true,
                'notes' => $request->notes,
            ]
        );

        return back()->with('success', 'Daily work log saved successfully!');
    }

    public function salaries()
    {
        $employee = $this->getEmployeeOrAbort();
        $salaries = $employee->salaries()->orderByDesc('year')->orderByDesc('month')->get();

        return view('employee.salaries', compact('employee', 'salaries'));
    }

    public function advances()
    {
        $employee = $this->getEmployeeOrAbort();
        $pendingAdvanceBalance = $employee->total_pending_advance;
        list($expectedCommission, $clientDetails) = $this->getExpectedCommissionAndClients($employee);

        $grossEarnings = 0;
        if ($employee->salary_type === 'salary') {
            $grossEarnings = $employee->fixed_salary;
        } elseif ($employee->salary_type === 'commission') {
            $grossEarnings = $expectedCommission;
        } else {
            $grossEarnings = $employee->fixed_salary + $expectedCommission;
        }
        $netExpectedPayout = max(0, $grossEarnings - $pendingAdvanceBalance);

        $advanceRequests = EmployeeAdvanceRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get();

        $advances = $employee->advances()->orderByDesc('advance_date')->get();

        return view('employee.advances', compact(
            'employee', 'pendingAdvanceBalance', 'advanceRequests', 'advances', 'netExpectedPayout'
        ));
    }

    public function holidays()
    {
        $employee = $this->getEmployeeOrAbort();
        $holidayRequests = EmployeeHolidayRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get();

        return view('employee.holidays', compact('employee', 'holidayRequests'));
    }

    public function storeAdvanceRequest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $employee = $this->getEmployeeOrAbort();

        $advanceRequest = EmployeeAdvanceRequest::create([
            'employee_id' => $employee->id,
            'amount' => $request->amount,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        // Send notifications to Admin and Main Admin roles
        $admins = \App\Models\User::role(['Admin', 'Main Admin'])->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'advance_request_submitted',
                'title' => 'New Advance Request',
                'message' => 'Employee "' . $employee->name . '" has submitted a new advance request of ₹' . number_format($request->amount, 2) . '.',
            ]);
        }

        return redirect()->route('employee.advances')->with('success', 'Advance request submitted successfully!');
    }

    public function storeHolidayRequest(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $employee = $this->getEmployeeOrAbort();

        $holidayRequest = EmployeeHolidayRequest::create([
            'employee_id' => $employee->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Send notifications to Admin and Main Admin roles
        $admins = \App\Models\User::role(['Admin', 'Main Admin'])->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'holiday_request_submitted',
                'title' => 'New Holiday Request',
                'message' => 'Employee "' . $employee->name . '" has requested holiday leave from ' . Carbon::parse($request->start_date)->format('d/m/Y') . ' to ' . Carbon::parse($request->end_date)->format('d/m/Y') . '.',
            ]);
        }

        return redirect()->route('employee.holidays')->with('success', 'Holiday request submitted successfully!');
    }
}
