<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientPackageHistory;
use App\Models\ClientGstHistory;
use App\Models\ClientManagerHistory;
use App\Models\ClientTimeline;
use App\Models\ClientNote;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected BillingService $billingService;
    protected CommissionService $commissionService;

    public function __construct(BillingService $billingService, CommissionService $commissionService)
    {
        $this->billingService = $billingService;
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $query = Client::with(['manager', 'assignedEmployee', 'billingCycles']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status (Default to 'active' if not provided)
        $currentStatus = $request->get('status', 'active');
        if ($currentStatus !== 'all') {
            $query->where('status', $currentStatus);
        }

        // Filter by manager
        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('assigned_employee_id', $request->employee_id);
        }

        // Filter by payment status
        if ($request->filled('payment_status') && $request->payment_status === 'due') {
            $query->paymentDue();
        }

        // Sorting
        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('direction', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $clients = $query->paginate(25)->appends($request->query());

        $managers = User::role(['Main Admin', 'Admin'])->active()->get();
        $employees = \App\Models\Employee::active()->get();

        return view('clients.index', compact('clients', 'managers', 'employees', 'currentStatus'));
    }

    public function create()
    {
        $managers = User::role(['Main Admin', 'Admin'])->active()->get();
        $employees = \App\Models\Employee::active()->get();

        return view('clients.create', compact('managers', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'mobile_secondary' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'joining_date' => 'required|date',
            'service_start_date' => 'required|date',
            'current_package' => 'required|numeric|min:0',
            'current_flipkart_gst' => 'nullable|integer|min:0',
            'current_meesho_gst' => 'nullable|integer|min:0',
            'work_location' => 'required|in:client_office,our_office,hybrid',
            'manager_id' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'advance_payment' => 'nullable|numeric|min:0',
            'assignments' => 'nullable|array',
            'assignments.*.employee_id' => 'nullable|exists:employees,id',
            'assignments.*.gst_count' => 'nullable|integer|min:0',
            'assignments.*.gst_platform' => 'nullable|string|in:flipkart,meesho',
            'assignments.*.commission_type' => 'nullable|in:fixed_amount,percentage',
            'assignments.*.custom_package_amount' => 'nullable|numeric|min:0',
            'assignments.*.commission_value' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['current_flipkart_gst'] = $validated['current_flipkart_gst'] ?? 0;
        $validated['current_meesho_gst'] = $validated['current_meesho_gst'] ?? 0;

        // Strip assignments from main client creation
        $clientData = collect($validated)->except(['assignments', 'advance_payment'])->toArray();
        $client = Client::create($clientData);

        // Generate billing cycles
        $this->billingService->generateBillingCycles($client);

        // Process advance payment if provided
        if ($request->filled('advance_payment') && (float)$request->advance_payment > 0) {
            $this->billingService->processPayment($client, (float)$request->advance_payment, [
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Advance payment received on client registration',
            ]);
        }

        // Create timeline entry
        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'client_created',
            'description' => 'Client created with package ₹' . number_format($client->current_package, 2),
            'created_by' => Auth::id(),
        ]);

        // Handle employee assignments
        if ($request->has('assignments') && is_array($request->assignments)) {
            $firstEmployeeId = null;
            foreach ($request->assignments as $assignData) {
                if (!empty($assignData['employee_id'])) {
                    if (!$firstEmployeeId) {
                        $firstEmployeeId = $assignData['employee_id'];
                    }

                    \App\Models\EmployeeClientAssignment::create([
                        'employee_id' => $assignData['employee_id'],
                        'client_id' => $client->id,
                        'assigned_date' => now(),
                        'status' => 'active',
                        'gst_count' => $assignData['gst_count'] ?? 0,
                        'gst_platform' => $assignData['gst_platform'] ?? null,
                        'commission_type' => $assignData['commission_type'] ?? 'fixed_amount',
                        'custom_package_amount' => $assignData['custom_package_amount'] ?: null,
                        'commission_value' => $assignData['commission_value'] ?? 0,
                    ]);
                }
            }

            // Set the first employee as assigned_employee_id on client for compatibility
            if ($firstEmployeeId) {
                $client->update(['assigned_employee_id' => $firstEmployeeId]);
            }
        }

        return redirect()->route('clients.show', $client)->with('success', 'Client created successfully!');
    }

    public function show(Client $client)
    {
        $client->load([
            'manager', 'assignedEmployee', 'packageHistory.changedByUser',
            'gstHistory.changedByUser', 'managerHistory.oldManager', 'managerHistory.newManager',
            'billingCycles', 'payments.receivedByUser', 'accounts', 'documents.uploadedByUser',
            'notes.createdByUser', 'timeline.createdByUser', 'followUps.createdByUser',
            'employeeAssignments.employee',
        ]);

        $outstandingBalance = $this->billingService->getOutstandingBalance($client);
        $advanceBalance = $this->billingService->getAdvanceBalance($client);

        return view('clients.show', compact('client', 'outstandingBalance', 'advanceBalance'));
    }

    public function edit(Client $client)
    {
        $managers = User::role(['Main Admin', 'Admin'])->active()->get();
        $employees = \App\Models\Employee::active()->get();

        return view('clients.edit', compact('client', 'managers', 'employees'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'mobile_secondary' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'work_location' => 'required|in:client_office,our_office,hybrid',
            'address' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully!');
    }

    public function changePackage(Request $request, Client $client)
    {
        $request->validate([
            'new_package' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldPackage = $client->current_package;
        $newPackage = $request->new_package;

        // Create history record
        ClientPackageHistory::create([
            'client_id' => $client->id,
            'old_package' => $oldPackage,
            'new_package' => $newPackage,
            'change_date' => now(),
            'changed_by' => Auth::id(),
            'reason' => $request->reason,
        ]);

        // Update client
        $client->update(['current_package' => $newPackage]);

        // Update billing cycles
        $this->billingService->updateCurrentCycleForPackageChange($client);

        // Recalculate commissions
        $this->commissionService->recalculateForPackageChange($client);

        // Timeline
        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'package_changed',
            'description' => 'Package changed from ₹' . number_format($oldPackage, 2) . ' to ₹' . number_format($newPackage, 2),
            'metadata' => ['old' => $oldPackage, 'new' => $newPackage, 'reason' => $request->reason],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', 'Package updated successfully!');
    }

    public function changeGst(Request $request, Client $client)
    {
        $request->validate([
            'gst_type' => 'required|in:flipkart,meesho',
            'new_amount' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $field = 'current_' . $request->gst_type . '_gst';
        $oldAmount = $client->$field;

        ClientGstHistory::create([
            'client_id' => $client->id,
            'gst_type' => $request->gst_type,
            'old_amount' => $oldAmount,
            'new_amount' => $request->new_amount,
            'change_date' => now(),
            'changed_by' => Auth::id(),
            'reason' => $request->reason,
        ]);

        $client->update([$field => $request->new_amount]);

        $this->billingService->updateCurrentCycleForPackageChange($client->fresh());

        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'gst_changed',
            'description' => ucfirst($request->gst_type) . ' GST count changed from ' . $oldAmount . ' to ' . $request->new_amount,
            'metadata' => ['type' => $request->gst_type, 'old' => $oldAmount, 'new' => $request->new_amount],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', 'GST updated successfully!');
    }

    public function changeManager(Request $request, Client $client)
    {
        $request->validate([
            'new_manager_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        ClientManagerHistory::create([
            'client_id' => $client->id,
            'old_manager_id' => $client->manager_id,
            'new_manager_id' => $request->new_manager_id,
            'change_date' => now(),
            'changed_by' => Auth::id(),
            'reason' => $request->reason,
        ]);

        $oldManager = $client->manager?->name ?? 'None';
        $client->update(['manager_id' => $request->new_manager_id]);
        $newManager = $client->fresh()->manager?->name ?? 'None';

        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'manager_changed',
            'description' => "Manager changed from {$oldManager} to {$newManager}",
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', 'Manager updated successfully!');
    }

    public function changeStatus(Request $request, Client $client)
    {
        $request->validate(['status' => 'required|in:active,inactive,archived']);

        $oldStatus = $client->status;
        $client->update(['status' => $request->status]);

        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'status_changed',
            'description' => "Status changed from {$oldStatus} to {$request->status}",
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', 'Status updated successfully!');
    }

    public function renew(Request $request, Client $client)
    {
        $request->validate([
            'package_option' => 'required|in:same,new',
            'new_package_amount' => 'nullable|numeric|min:0|required_if:package_option,new',
            'billing_start' => 'required|date',
            'billing_end' => 'required|date|after_or_equal:billing_start',
            'collect_payment' => 'nullable|boolean',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,upi,cheque,other',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldPackage = (float) $client->current_package;
        $packageAmount = $oldPackage;

        if ($request->package_option === 'new' && $request->filled('new_package_amount')) {
            $packageAmount = (float) $request->new_package_amount;
            if ($packageAmount != $oldPackage) {
                // Record package history
                ClientPackageHistory::create([
                    'client_id' => $client->id,
                    'old_package' => $oldPackage,
                    'new_package' => $packageAmount,
                    'change_date' => now(),
                    'changed_by' => Auth::id(),
                    'reason' => 'Package adjusted during client renewal',
                ]);

                $client->update(['current_package' => $packageAmount]);
                $this->commissionService->recalculateForPackageChange($client);
            }
        }

        // Make sure client is active
        if ($client->status !== 'active') {
            $client->update(['status' => 'active']);
        }

        // Calculate GST charges if any
        $flipkartRate = (float) \App\Models\Setting::get('price_per_flipkart_gst', 0);
        $meeshoRate = (float) \App\Models\Setting::get('price_per_meesho_gst', 0);
        $flipkartCharge = ($client->current_flipkart_gst ?? 0) * $flipkartRate;
        $meeshoCharge = ($client->current_meesho_gst ?? 0) * $meeshoRate;
        $totalDue = $packageAmount + $flipkartCharge + $meeshoCharge;

        // Create the new 1-month billing cycle
        $cycle = \App\Models\ClientBillingCycle::create([
            'client_id' => $client->id,
            'billing_start' => $request->billing_start,
            'billing_end' => $request->billing_end,
            'package_amount' => $packageAmount,
            'flipkart_gst' => $flipkartCharge,
            'meesho_gst' => $meeshoCharge,
            'total_due' => $totalDue,
            'total_paid' => 0,
            'balance' => $totalDue,
            'status' => 'pending',
        ]);

        // Process payment if collected on renewal
        if ($request->boolean('collect_payment') && $request->filled('payment_amount') && (float)$request->payment_amount > 0) {
            $paymentData = [
                'payment_date' => $request->payment_date ?? now()->format('Y-m-d'),
                'payment_method' => $request->payment_method ?? 'cash',
                'notes' => $request->notes ?? 'Payment received during renewal',
            ];
            $this->billingService->processPayment($client, (float)$request->payment_amount, $paymentData, $cycle->id);
        }

        // Record timeline
        ClientTimeline::create([
            'client_id' => $client->id,
            'event_type' => 'client_renewed',
            'description' => 'Client renewed for cycle ' . \Carbon\Carbon::parse($request->billing_start)->format('d M Y') . ' to ' . \Carbon\Carbon::parse($request->billing_end)->format('d M Y') . ' with package ₹' . number_format($packageAmount, 2),
            'metadata' => [
                'billing_cycle_id' => $cycle->id,
                'package_amount' => $packageAmount,
                'package_option' => $request->package_option,
                'payment_collected' => $request->boolean('collect_payment') ? (float)$request->payment_amount : 0,
            ],
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Client renewed successfully! Next 1-month billing cycle has been created.');
    }

    public function destroy(Client $client)
    {
        if ($client->status !== 'inactive') {
            return redirect()->back()->with('error', 'Only inactive clients can be deleted. Please set client status to Inactive first.');
        }

        $clientName = $client->name;

        \Illuminate\Support\Facades\DB::transaction(function () use ($client) {
            $client->employeeAssignments()->delete();
            $client->billingCycles()->delete();
            $client->payments()->delete();
            $client->accounts()->delete();
            $client->documents()->delete();
            $client->notes()->delete();
            $client->timeline()->delete();
            $client->packageHistory()->delete();
            $client->gstHistory()->delete();
            $client->managerHistory()->delete();
            $client->followUps()->delete();

            $client->delete();
        });

        return redirect()->route('clients.index', ['status' => 'inactive'])->with('success', 'Inactive client "' . $clientName . '" deleted successfully.');
    }
}
