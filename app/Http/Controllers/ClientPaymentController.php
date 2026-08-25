<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientPayment;
use App\Services\BillingService;
use Illuminate\Support\Facades\Auth;

class ClientPaymentController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $query = ClientPayment::with(['client', 'receivedByUser', 'billingCycle']);

        if ($request->filled('search')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->orderByDesc('payment_date')->paginate(25)->appends($request->query());

        return view('payments.index', compact('payments'));
    }

    public function create(Client $client)
    {
        $client->load('billingCycles');
        $outstandingBalance = $this->billingService->getOutstandingBalance($client);
        $advanceBalance = $this->billingService->getAdvanceBalance($client);

        return view('payments.create', compact('client', 'outstandingBalance', 'advanceBalance'));
    }

    public function store(Request $request, Client $client)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,upi,cheque,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'billing_cycle_id' => 'nullable|exists:client_billing_cycles,id',
        ]);

        $this->billingService->processPayment($client, $request->amount, $request->only([
            'payment_date', 'payment_method', 'reference_number', 'notes'
        ]), $request->billing_cycle_id);

        return redirect()->route('clients.show', $client)->with('success', '₹' . number_format($request->amount, 2) . ' payment received successfully!');
    }
}
