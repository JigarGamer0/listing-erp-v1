<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBillingCycle;
use App\Models\ClientPayment;
use App\Models\ClientTimeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate billing cycles for a client from their service start date to now
     */
    public function generateBillingCycles(Client $client): void
    {
        $startDate = Carbon::parse($client->service_start_date);
        $now = Carbon::now();

        // Generate cycles up to the current period
        while ($startDate->copy()->addMonth()->subDay()->lte($now->copy()->addMonth())) {
            $cycleEnd = $startDate->copy()->addMonth()->subDay();

            $exists = ClientBillingCycle::where('client_id', $client->id)
                ->where('billing_start', $startDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                $flipkartRate = (float) \App\Models\Setting::get('price_per_flipkart_gst', 0);
                $meeshoRate = (float) \App\Models\Setting::get('price_per_meesho_gst', 0);
                $flipkartCharge = $client->current_flipkart_gst * $flipkartRate;
                $meeshoCharge = $client->current_meesho_gst * $meeshoRate;
                $totalDue = $client->current_package + $flipkartCharge + $meeshoCharge;

                ClientBillingCycle::create([
                    'client_id' => $client->id,
                    'billing_start' => $startDate->format('Y-m-d'),
                    'billing_end' => $cycleEnd->format('Y-m-d'),
                    'package_amount' => $client->current_package,
                    'flipkart_gst' => $flipkartCharge,
                    'meesho_gst' => $meeshoCharge,
                    'total_due' => $totalDue,
                    'total_paid' => 0,
                    'balance' => $totalDue,
                    'status' => $cycleEnd->isPast() ? 'overdue' : 'pending',
                ]);
            }

            $startDate->addMonth();
        }
    }

    /**
     * Generate a single upcoming billing cycle for a client
     */
    public function generateNextBillingCycle(Client $client): ?ClientBillingCycle
    {
        $lastCycle = $client->billingCycles()->orderByDesc('billing_end')->first();

        if ($lastCycle) {
            $nextStart = Carbon::parse($lastCycle->billing_end)->addDay();
        } else {
            $nextStart = Carbon::parse($client->service_start_date);
        }

        $nextEnd = $nextStart->copy()->addMonth()->subDay();

        $flipkartRate = (float) \App\Models\Setting::get('price_per_flipkart_gst', 0);
        $meeshoRate = (float) \App\Models\Setting::get('price_per_meesho_gst', 0);
        $flipkartCharge = $client->current_flipkart_gst * $flipkartRate;
        $meeshoCharge = $client->current_meesho_gst * $meeshoRate;
        $totalDue = $client->current_package + $flipkartCharge + $meeshoCharge;

        return ClientBillingCycle::create([
            'client_id' => $client->id,
            'billing_start' => $nextStart->format('Y-m-d'),
            'billing_end' => $nextEnd->format('Y-m-d'),
            'package_amount' => $client->current_package,
            'flipkart_gst' => $flipkartCharge,
            'meesho_gst' => $meeshoCharge,
            'total_due' => $totalDue,
            'total_paid' => 0,
            'balance' => $totalDue,
            'status' => 'pending',
        ]);
    }

    /**
     * Process a payment for a client
     * Automatically distributes payment across pending billing cycles (oldest first)
     */
    public function processPayment(Client $client, float $amount, array $paymentData, int $billingCycleId = null): ClientPayment
    {
        return DB::transaction(function () use ($client, $amount, $paymentData, $billingCycleId) {
            if ($billingCycleId) {
                $cycle = $client->billingCycles()->findOrFail($billingCycleId);
                $payment = ClientPayment::create([
                    'client_id' => $client->id,
                    'billing_cycle_id' => $cycle->id,
                    'amount' => $amount,
                    'payment_date' => $paymentData['payment_date'],
                    'payment_method' => $paymentData['payment_method'] ?? 'cash',
                    'reference_number' => $paymentData['reference_number'] ?? null,
                    'notes' => $paymentData['notes'] ?? null,
                    'received_by' => Auth::id(),
                ]);
                $cycle->recalculate();

                // Create timeline entry
                ClientTimeline::create([
                    'client_id' => $client->id,
                    'event_type' => 'payment_received',
                    'description' => 'Received payment of ₹' . number_format($amount, 2) . ' for cycle ' . Carbon::parse($cycle->billing_start)->format('d/m/Y') . ' - ' . Carbon::parse($cycle->billing_end)->format('d/m/Y'),
                    'created_by' => Auth::id(),
                ]);

                return $payment;
            }

            $remainingAmount = $amount;

            // Get pending billing cycles ordered by oldest first
            $pendingCycles = $client->billingCycles()
                ->whereIn('status', ['overdue', 'pending', 'partial'])
                ->orderBy('billing_start')
                ->get();

            $firstCycleId = null;

            foreach ($pendingCycles as $cycle) {
                if ($remainingAmount <= 0) break;

                $cycleBalance = $cycle->balance;
                $payForCycle = min($remainingAmount, $cycleBalance);

                if ($payForCycle > 0) {
                    // Create payment record for this cycle portion
                    $payment = ClientPayment::create([
                        'client_id' => $client->id,
                        'billing_cycle_id' => $cycle->id,
                        'amount' => $payForCycle,
                        'payment_date' => $paymentData['payment_date'],
                        'payment_method' => $paymentData['payment_method'] ?? 'cash',
                        'reference_number' => $paymentData['reference_number'] ?? null,
                        'notes' => $paymentData['notes'] ?? null,
                        'received_by' => Auth::id(),
                    ]);

                    if (!$firstCycleId) $firstCycleId = $cycle->id;

                    $cycle->recalculate();
                    $remainingAmount -= $payForCycle;
                }
            }

            // If there's still remaining amount, it's an advance payment
            if ($remainingAmount > 0) {
                // Apply to the next upcoming cycle or create as advance
                $nextCycle = $client->billingCycles()
                    ->where('status', 'pending')
                    ->orderBy('billing_start')
                    ->first();

                if (!$nextCycle) {
                    $nextCycle = $this->generateNextBillingCycle($client);
                }

                $payment = ClientPayment::create([
                    'client_id' => $client->id,
                    'billing_cycle_id' => $nextCycle->id,
                    'amount' => $remainingAmount,
                    'payment_date' => $paymentData['payment_date'],
                    'payment_method' => $paymentData['payment_method'] ?? 'cash',
                    'reference_number' => $paymentData['reference_number'] ?? null,
                    'notes' => $paymentData['notes'] ?? ($paymentData['notes'] ?? '') . ' (Advance)',
                    'received_by' => Auth::id(),
                ]);

                $nextCycle->recalculate();
            }

            // Create timeline entry
            ClientTimeline::create([
                'client_id' => $client->id,
                'event_type' => 'payment_received',
                'description' => 'Received payment of ₹' . number_format($amount, 2),
                'created_by' => Auth::id(),
            ]);

            // Return the first payment created
            return ClientPayment::where('client_id', $client->id)
                ->latest()
                ->first();
        });
    }

    /**
     * Get client's total outstanding balance
     */
    public function getOutstandingBalance(Client $client): float
    {
        return $client->billingCycles()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    /**
     * Get client's advance balance
     */
    public function getAdvanceBalance(Client $client): float
    {
        return abs($client->billingCycles()
            ->where('status', 'advance')
            ->sum('balance'));
    }

    /**
     * Update billing cycles when package changes mid-cycle
     */
    public function updateCurrentCycleForPackageChange(Client $client): void
    {
        $now = Carbon::now();
        $flipkartRate = (float) \App\Models\Setting::get('price_per_flipkart_gst', 0);
        $meeshoRate = (float) \App\Models\Setting::get('price_per_meesho_gst', 0);
        $flipkartCharge = $client->current_flipkart_gst * $flipkartRate;
        $meeshoCharge = $client->current_meesho_gst * $meeshoRate;
        $totalDue = $client->current_package + $flipkartCharge + $meeshoCharge;

        // Find the current active billing cycle
        $currentCycle = $client->billingCycles()
            ->where('billing_start', '<=', $now)
            ->where('billing_end', '>=', $now)
            ->whereIn('status', ['pending', 'partial'])
            ->first();

        if ($currentCycle) {
            $currentCycle->update([
                'package_amount' => $client->current_package,
                'flipkart_gst' => $flipkartCharge,
                'meesho_gst' => $meeshoCharge,
                'total_due' => $totalDue,
                'balance' => $totalDue - $currentCycle->total_paid,
            ]);
            $currentCycle->recalculate();
        }

        // Update future pending cycles too
        $futureCycles = $client->billingCycles()
            ->where('billing_start', '>', $now)
            ->where('status', 'pending')
            ->get();

        foreach ($futureCycles as $cycle) {
            $cycle->update([
                'package_amount' => $client->current_package,
                'flipkart_gst' => $flipkartCharge,
                'meesho_gst' => $meeshoCharge,
                'total_due' => $totalDue,
                'balance' => $totalDue,
            ]);
        }
    }

    /**
     * Mark overdue billing cycles
     */
    public function markOverdueCycles(): int
    {
        return ClientBillingCycle::where('billing_end', '<', Carbon::now())
            ->where('status', 'pending')
            ->update(['status' => 'overdue']);
    }
}
