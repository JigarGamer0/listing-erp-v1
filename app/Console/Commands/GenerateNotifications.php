<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\ClientBillingCycle;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\FollowUp;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate';
    protected $description = 'Generate daily notifications for payment dues, renewals, and follow-ups';

    public function handle()
    {
        $now = Carbon::now();
        $adminUsers = User::role(['Main Admin', 'Admin'])->get();

        // Payment Due Notifications
        $overdueCycles = ClientBillingCycle::with('client')
            ->where('status', 'overdue')
            ->get()
            ->groupBy('client_id');

        foreach ($overdueCycles as $clientId => $cycles) {
            $client = $cycles->first()->client;
            $totalDue = $cycles->sum('balance');
            $monthsCount = $cycles->count();

            foreach ($adminUsers as $user) {
                $exists = Notification::where('user_id', $user->id)
                    ->where('type', 'payment_due')
                    ->whereDate('created_at', today())
                    ->whereJsonContains('data->client_id', $clientId)
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'payment_due',
                        'title' => 'Payment Due: ' . $client->name,
                        'message' => "₹" . number_format($totalDue, 2) . " pending for {$monthsCount} month(s)",
                        'data' => ['client_id' => $clientId, 'amount' => $totalDue],
                    ]);
                }
            }
        }

        // Upcoming Renewal Notifications (7 days ahead)
        $upcomingRenewals = ClientBillingCycle::with('client')
            ->where('billing_end', '>=', $now)
            ->where('billing_end', '<=', $now->copy()->addDays(7))
            ->where('status', 'pending')
            ->get();

        foreach ($upcomingRenewals as $cycle) {
            foreach ($adminUsers as $user) {
                $exists = Notification::where('user_id', $user->id)
                    ->where('type', 'renewal')
                    ->whereDate('created_at', today())
                    ->whereJsonContains('data->cycle_id', $cycle->id)
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'renewal',
                        'title' => 'Upcoming Renewal: ' . $cycle->client->name,
                        'message' => 'Billing cycle ends on ' . $cycle->billing_end->format('d/m/Y'),
                        'data' => ['client_id' => $cycle->client_id, 'cycle_id' => $cycle->id],
                    ]);
                }
            }
        }

        // Pending Salary Notifications
        $pendingSalaries = EmployeeSalary::with('employee')
            ->where('status', 'pending')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->get();

        foreach ($pendingSalaries as $salary) {
            foreach ($adminUsers as $user) {
                $exists = Notification::where('user_id', $user->id)
                    ->where('type', 'pending_salary')
                    ->whereDate('created_at', today())
                    ->whereJsonContains('data->salary_id', $salary->id)
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'pending_salary',
                        'title' => 'Pending Salary: ' . $salary->employee->name,
                        'message' => "₹" . number_format($salary->net_payable, 2) . " salary pending for " . $salary->month_name,
                        'data' => ['salary_id' => $salary->id, 'employee_id' => $salary->employee_id],
                    ]);
                }
            }
        }

        // Follow-up Notifications
        $followUps = FollowUp::with('client')
            ->where('status', 'pending')
            ->where('follow_up_date', '<=', $now->copy()->addDay())
            ->get();

        foreach ($followUps as $followUp) {
            foreach ($adminUsers as $user) {
                Notification::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'follow_up',
                    'data' => json_encode(['follow_up_id' => $followUp->id]),
                ], [
                    'title' => 'Follow-up: ' . $followUp->client->name,
                    'message' => $followUp->note ?? 'Follow-up scheduled for ' . $followUp->follow_up_date->format('d/m/Y'),
                ]);
            }
        }

        $this->info('Notifications generated successfully.');
    }
}
