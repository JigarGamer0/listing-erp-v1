<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeClientAssignment;
use App\Models\Client;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $query = Employee::with(['user', 'activeAssignments']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('name')->paginate(25)->appends($request->query());

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email',
            'joining_date' => 'required|date',
            'role_title' => 'nullable|string|max:100',
            'salary_type' => 'required|in:fixed,package_based,both',
            'fixed_salary' => 'nullable|numeric|min:0',
            'commission_type' => 'required|in:fixed_amount,percentage',
            'commission_value' => 'nullable|numeric|min:0',
            'create_login' => 'nullable|boolean',
            'username' => 'nullable|required_if:create_login,1|unique:users,username',
            'password' => 'nullable|required_if:create_login,1|min:8',
        ]);

        // Optionally create a user account for the employee
        $userId = null;
        if ($request->boolean('create_login') && $request->filled('username')) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?? $request->username . '@listingerp.local',
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'must_change_password' => true,
                'status' => 'active',
            ]);
            $user->assignRole('Admin');
            $userId = $user->id;
        }

        $employee = Employee::create([
            'user_id' => $userId,
            'name' => $request->name,
            'phone' => $request->phone,
            'joining_date' => $request->joining_date,
            'role_title' => $request->role_title,
            'salary_type' => $request->salary_type,
            'fixed_salary' => $request->fixed_salary ?? 0,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value ?? 0,
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created successfully!');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'activeAssignments.client',
            'commissions.client',
            'salaries',
            'advances',
            'salaryDeductions.createdByUser',
        ]);

        // Calculate commission details per client
        $clientCommissions = [];
        foreach ($employee->activeAssignments as $assignment) {
            $client = $assignment->client;
            if (!$client) continue;
            $commissionAmount = $employee->calculateCommissionForClient($client);
            $clientCommissions[] = [
                'client' => $client,
                'assignment' => $assignment,
                'commission_amount' => $commissionAmount,
            ];
        }

        $clients = Client::active()->orderBy('name')->get();

        return view('employees.show', compact('employee', 'clientCommissions', 'clients'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role_title' => 'nullable|string|max:100',
            'salary_type' => 'required|in:fixed,package_based,both',
            'fixed_salary' => 'nullable|numeric|min:0',
            'commission_type' => 'required|in:fixed_amount,percentage',
            'commission_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,archived',
        ];

        if ($employee->user) {
            $rules['username'] = 'nullable|string|unique:users,username,' . $employee->user->id;
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['create_login'] = 'nullable|boolean';
            $rules['username'] = 'nullable|required_if:create_login,1|unique:users,username';
            $rules['password'] = 'nullable|required_if:create_login,1|min:8';
        }

        $request->validate($rules);

        // Update employee details
        $employee->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'role_title' => $request->role_title,
            'salary_type' => $request->salary_type,
            'fixed_salary' => $request->fixed_salary ?? 0,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value ?? 0,
            'status' => $request->status,
        ]);

        // Manage User Account credentials
        if ($employee->user) {
            $userData = [];
            if ($request->filled('username')) {
                $userData['username'] = $request->username;
            }
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            if (!empty($userData)) {
                $employee->user->update($userData);
            }
        } else {
            if ($request->boolean('create_login') && $request->filled('username')) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->username . '@listingerp.local',
                    'username' => $request->username,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'must_change_password' => false,
                    'status' => 'active',
                ]);
                $user->assignRole('Employee'); // Default to Employee role for new employee logins
                $employee->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully!');
    }

    public function assignClient(Request $request, Employee $employee)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'gst_count' => 'nullable|integer|min:0',
            'gst_platform' => 'nullable|string|in:flipkart,meesho',
            'custom_commission_type' => 'nullable|in:fixed_amount,percentage',
            'custom_commission_value' => 'nullable|numeric|min:0',
            'custom_package_amount' => 'nullable|numeric|min:0',
        ]);

        // Check if already assigned
        $existing = EmployeeClientAssignment::where('employee_id', $employee->id)
            ->where('client_id', $request->client_id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->withErrors(['client_id' => 'This client is already assigned to this employee.']);
        }

        EmployeeClientAssignment::create([
            'employee_id' => $employee->id,
            'client_id' => $request->client_id,
            'assigned_date' => now(),
            'status' => 'active',
            'gst_count' => $request->gst_count ?? 0,
            'gst_platform' => $request->gst_platform,
            'commission_type' => $request->custom_commission_type,
            'commission_value' => $request->custom_commission_value ?? 0,
            'custom_package_amount' => $request->custom_package_amount ?: null,
        ]);

        // Update client's assigned_employee_id to employee's ID (NOT user_id)
        Client::where('id', $request->client_id)->update(['assigned_employee_id' => $employee->id]);

        return redirect()->route('employees.show', $employee)->with('success', 'Client assigned successfully!');
    }

    public function unassignClient(Employee $employee, EmployeeClientAssignment $assignment)
    {
        $assignment->update([
            'unassigned_date' => now(),
            'status' => 'inactive',
        ]);

        // Clear client's assigned_employee_id
        Client::where('id', $assignment->client_id)->update(['assigned_employee_id' => null]);

        return redirect()->route('employees.show', $employee)->with('success', 'Client unassigned successfully!');
    }

    public function destroy(Employee $employee)
    {
        $employeeName = $employee->name;

        \Illuminate\Support\Facades\DB::transaction(function () use ($employee) {
            // Unassign all active clients
            EmployeeClientAssignment::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'inactive',
                    'unassigned_date' => now(),
                ]);

            // Clear client's assigned_employee_id if pointing to this employee
            Client::where('assigned_employee_id', $employee->id)->update(['assigned_employee_id' => null]);

            // Deactivate or soft-delete associated user account if one exists
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $user->update(['status' => 'inactive']);
                    $user->delete();
                }
            }

            // Delete the employee
            $employee->delete();
        });

        return redirect()->route('employees.index')->with('success', 'Employee "' . $employeeName . '" deleted successfully and active clients were safely unassigned.');
    }
}
