<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Client;
use App\Models\EmployeeClientAssignment;
use App\Models\EmployeeDailyWorkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WorkTrackerController extends Controller
{
    /**
     * Daily Work Tracker (Date Specific)
     * Accurately respects assignment date ranges:
     * - Only shows clients assigned on or before the selected date.
     * - If client was unassigned after that date, it still shows in historical views for that date.
     */
    public function index(Request $request)
    {
        $dateStr = $request->input('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        // Fetch assignments active on this exact selected date
        $assignmentQuery = EmployeeClientAssignment::with(['employee', 'client'])
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('assigned_date')
                  ->orWhereDate('assigned_date', '<=', $dateStr);
            })
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('unassigned_date')
                  ->orWhereDate('unassigned_date', '>=', $dateStr);
            })
            ->whereHas('client'); // Ensure client exists

        // If logged-in user is an employee, only show their assigned clients
        $user = Auth::user();
        if ($user && $user->hasRole('Employee') && $user->employee) {
            $assignmentQuery->where('employee_id', $user->employee->id);
        }

        $assignments = $assignmentQuery->get();

        // Fetch all logs for the selected date
        $logs = EmployeeDailyWorkLog::whereDate('log_date', $dateStr)
            ->get()
            ->keyBy(function ($item) {
                return $item->employee_id . '-' . $item->client_id;
            });

        // Group assignments by employee
        $trackerData = [];
        $totalClientsAssigned = 0;
        $completedClientsToday = 0;
        $totalListingsToday = 0;

        foreach ($assignments as $assign) {
            $employee = $assign->employee;
            $client = $assign->client;
            if (!$employee || !$client) continue;

            $totalClientsAssigned++;
            $logKey = $employee->id . '-' . $client->id;
            $logEntry = $logs->get($logKey);

            $isDone = false;
            $listings = 0;
            $notes = '';
            $loggedAt = null;

            if ($logEntry) {
                $isDone = (bool) $logEntry->is_done;
                $listings = (int) $logEntry->listings_count;
                $notes = $logEntry->notes;
                $loggedAt = $logEntry->updated_at;

                if ($isDone) {
                    $completedClientsToday++;
                }
                $totalListingsToday += $listings;
            }

            if (!isset($trackerData[$employee->id])) {
                $trackerData[$employee->id] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'clients' => [],
                    'done_count' => 0,
                    'total_count' => 0,
                    'listings_sum' => 0,
                ];
            }

            $trackerData[$employee->id]['clients'][] = [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'is_done' => $isDone,
                'listings' => $listings,
                'notes' => $notes,
                'logged_at' => $loggedAt,
                'gst_platform' => $assign->gst_platform,
                'gst_count' => $assign->gst_count,
            ];

            $trackerData[$employee->id]['total_count']++;
            if ($isDone) {
                $trackerData[$employee->id]['done_count']++;
            }
            $trackerData[$employee->id]['listings_sum'] += $listings;
        }

        // Calculate progress percentage
        $progressPercent = $totalClientsAssigned > 0 
            ? round(($completedClientsToday / $totalClientsAssigned) * 100) 
            : 0;

        return view('admin.work-tracker.index', compact(
            'trackerData', 'dateStr', 'progressPercent', 
            'totalClientsAssigned', 'completedClientsToday', 'totalListingsToday'
        ));
    }

    /**
     * Monthly Work History View
     * Full calendar and day-by-day audit showing what work was done on every day of the month.
     */
    public function monthlyHistory(Request $request)
    {
        $user = Auth::user();
        $isEmployee = $user && $user->hasRole('Employee') && $user->employee;

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $employeeId = $isEmployee ? $user->employee->id : $request->input('employee_id');
        $clientId = $request->input('client_id');

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // Query logs for this month
        $logQuery = EmployeeDailyWorkLog::with(['employee', 'client'])
            ->whereBetween('log_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);

        if ($employeeId) {
            $logQuery->where('employee_id', $employeeId);
        }
        if ($clientId) {
            $logQuery->where('client_id', $clientId);
        }

        $monthlyLogs = $logQuery->orderBy('log_date', 'desc')
            ->orderBy('employee_id')
            ->get();

        // Statistics
        $totalListingsInMonth = $monthlyLogs->sum('listings_count');
        $totalCompletedTasks = $monthlyLogs->where('is_done', true)->count();
        $activeDaysWorked = $monthlyLogs->pluck('log_date')->unique()->count();

        // Group logs by Date
        $logsByDate = [];
        for ($d = $daysInMonth; $d >= 1; $d--) {
            $currentDayDate = Carbon::create($year, $month, $d)->format('Y-m-d');
            $logsByDate[$currentDayDate] = $monthlyLogs->filter(function ($log) use ($currentDayDate) {
                return Carbon::parse($log->log_date)->format('Y-m-d') === $currentDayDate;
            });
        }

        // Dropdown lists
        $employees = $isEmployee ? collect([$user->employee]) : Employee::active()->orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        // Month selector options
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'num' => $m,
                'name' => Carbon::create($year, $m, 1)->format('F'),
            ];
        }

        $years = [now()->year - 1, now()->year, now()->year + 1];

        return view('admin.work-tracker.monthly-history', compact(
            'month', 'year', 'employeeId', 'clientId', 'daysInMonth',
            'monthlyLogs', 'logsByDate', 'employees', 'clients', 'months', 'years',
            'totalListingsInMonth', 'totalCompletedTasks', 'activeDaysWorked', 'isEmployee'
        ));
    }
}
