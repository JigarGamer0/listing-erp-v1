<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientAccount;

class ClientAccountController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'platform' => 'required|string|max:100',
            'store_name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'login_password' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->accounts()->create($request->only([
            'platform', 'store_name', 'login_id', 'login_password', 'notes'
        ]));

        return redirect()->route('clients.show', $client)->with('success', 'Account added successfully!');
    }

    public function update(Request $request, Client $client, ClientAccount $account)
    {
        $request->validate([
            'platform' => 'required|string|max:100',
            'store_name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'login_password' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only(['platform', 'store_name', 'login_id', 'notes']);
        if ($request->filled('login_password')) {
            $data['login_password'] = $request->login_password;
        }

        $account->update($data);

        return redirect()->route('clients.show', $client)->with('success', 'Account updated successfully!');
    }

    public function destroy(Client $client, ClientAccount $account)
    {
        $account->delete();
        return redirect()->route('clients.show', $client)->with('success', 'Account removed successfully!');
    }
}
