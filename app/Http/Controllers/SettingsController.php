<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            'price_per_flipkart_gst' => 'required|numeric|min:0',
            'price_per_meesho_gst' => 'required|numeric|min:0',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        Setting::set('company_name', $request->company_name, 'general');
        Setting::set('currency', $request->currency, 'general');
        Setting::set('timezone', $request->timezone, 'general');
        Setting::set('date_format', $request->date_format, 'general');
        Setting::set('price_per_flipkart_gst', $request->price_per_flipkart_gst, 'general');
        Setting::set('price_per_meesho_gst', $request->price_per_meesho_gst, 'general');

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path, 'general', 'file');
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully!');
    }

    public function users()
    {
        $users = User::with('roles')->get();
        return view('settings.users', compact('users'));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:100|unique:users,username',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Main Admin,Admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'must_change_password' => true,
            'status' => 'active',
        ]);

        $user->assignRole($request->role);

        return redirect()->route('settings.users')->with('success', 'User created successfully!');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'role' => 'required|in:Main Admin,Admin',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'status']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->route('settings.users')->with('success', 'User updated successfully!');
    }
}
