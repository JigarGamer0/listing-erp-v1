<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Investor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Investment::with('investor');

        if ($request->filled('search')) {
            $query->whereHas('investor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('investor_id')) {
            $query->where('investor_id', $request->investor_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $investments = $query->orderByDesc('investment_date')->paginate(25)->appends($request->query());

        // All investors for dropdown filter
        $allInvestors = Investor::active()->orderBy('name')->get();

        // Investors who have uncleared investments (for clear modal)
        $unclearedInvestors = Investor::whereHas('investments', function ($q) {
            $q->where('status', 'uncleared');
        })->orderBy('name')->get();

        return view('investments.index', compact('investments', 'allInvestors', 'unclearedInvestors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'amount' => 'required|numeric|min:0.01',
            'investment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Investment::create([
            'investor_id' => $request->investor_id,
            'amount' => $request->amount,
            'investment_date' => $request->investment_date,
            'notes' => $request->notes,
            'status' => 'uncleared',
        ]);

        return redirect()->route('investments.index')->with('success', 'Investment recorded successfully!');
    }

    public function getUncleared(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
        ]);

        $entries = Investment::where('investor_id', $request->investor_id)
            ->where('status', 'uncleared')
            ->orderBy('investment_date')
            ->get();

        return response()->json($entries);
    }

    public function clear(Request $request)
    {
        $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'investment_ids' => 'required|array',
            'investment_ids.*' => 'exists:investments,id',
            'clear_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $investor = Investor::findOrFail($request->investor_id);

        $investments = Investment::whereIn('id', $request->investment_ids)
            ->where('investor_id', $request->investor_id)
            ->where('status', 'uncleared')
            ->get();

        if ($investments->isEmpty()) {
            return back()->withErrors(['investment_ids' => 'No matching uncleared investments found.']);
        }

        DB::transaction(function () use ($investments, $request, $investor) {
            // Find or create "Investor Payout" expense category
            $category = ExpenseCategory::firstOrCreate(
                ['name' => 'Investor Payout'],
                ['description' => 'Payouts made to clear investor balances', 'status' => 'active']
            );

            foreach ($investments as $inv) {
                // Create an Expense record
                $expense = Expense::create([
                    'title' => 'Cleared Investment - ' . $investor->name,
                    'category_id' => $category->id,
                    'amount' => $inv->amount,
                    'expense_date' => $request->clear_date,
                    'type' => 'one_time',
                    'notes' => $request->notes ?? ('Cleared investment entry of ' . $inv->investment_date->format('d/m/Y') . '. Original notes: ' . $inv->notes),
                    'created_by' => Auth::id(),
                ]);

                // Update investment to cleared and associate expense
                $inv->update([
                    'status' => 'cleared',
                    'expense_id' => $expense->id,
                ]);
            }
        });

        return redirect()->route('investments.index')->with('success', 'Selected investments cleared and converted to expenses successfully!');
    }
}
