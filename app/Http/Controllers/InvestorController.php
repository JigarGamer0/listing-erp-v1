<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use Illuminate\Support\Facades\Auth;

class InvestorController extends Controller
{
    public function index(Request $request)
    {
        $query = Investor::withCount(['investments', 'investments as uncleared_count' => function ($q) {
            $q->where('status', 'uncleared');
        }]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $investors = $query->orderBy('name')->paginate(25)->appends($request->query());

        return view('investors.index', compact('investors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Investor::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('investors.index')->with('success', 'Investor created successfully!');
    }

    public function update(Request $request, Investor $investor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $investor->update($request->only(['name', 'mobile', 'email', 'address', 'notes', 'status']));

        return redirect()->route('investors.index')->with('success', 'Investor updated successfully!');
    }

    // AJAX: return all active investors as JSON (for investment form dropdown)
    public function apiList()
    {
        $investors = Investor::active()->orderBy('name')->get(['id', 'name', 'mobile']);
        return response()->json($investors);
    }
}
