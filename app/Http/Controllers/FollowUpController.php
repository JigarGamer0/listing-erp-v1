<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowUp;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'follow_up_date' => 'required|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
        ]);

        FollowUp::create([
            'client_id' => $request->client_id,
            'follow_up_date' => $request->follow_up_date,
            'note' => $request->note,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Follow-up scheduled successfully!');
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $followUp->update(['status' => $request->status]);

        return back()->with('success', 'Follow-up updated successfully!');
    }
}
