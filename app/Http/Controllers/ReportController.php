<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(): View
    {
        Appointment::markReadyForNoShow();

        $user = request()->user();

        $appointments = Appointment::query()->visibleTo($user);

        $reports = [
            'total' => (clone $appointments)->count(),
            'daily' => (clone $appointments)->whereDate('appointment_date', today())->count(),
            'completed' => (clone $appointments)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $appointments)->where('status', Appointment::STATUS_CANCELLED)->count(),
            'no_show' => (clone $appointments)->where('status', Appointment::STATUS_NO_SHOW)->count(),
            'clients' => $user->isStaff()
                ? (clone $appointments)->distinct('client_id')->count('client_id')
                : Client::count(),
        ];

        $staffActivity = User::query()
            ->where('role', User::ROLE_STAFF)
            ->withCount([
                'assignedAppointments',
                'serviceRecords',
                'assignedAppointments as completed_appointments_count' => fn ($query) => $query
                    ->where('status', Appointment::STATUS_COMPLETED),
            ])
            ->orderByDesc('assigned_appointments_count')
            ->get();

        $trendMonths = collect(range(5, 1))
            ->map(fn (int $monthsAgo) => Carbon::now()->subMonths($monthsAgo)->startOfMonth())
            ->push(Carbon::now()->startOfMonth());

        $monthlyTrend = $trendMonths->map(function (Carbon $month) use ($appointments) {
            return [
                'label' => $month->format('M'),
                'count' => (clone $appointments)
                    ->whereYear('appointment_date', $month->year)
                    ->whereMonth('appointment_date', $month->month)
                    ->count(),
            ];
        });

        $statusDistribution = collect(Appointment::STATUSES)->map(function (string $status) use ($appointments) {
            return [
                'label' => $status,
                'count' => (clone $appointments)->where('status', $status)->count(),
            ];
        });

        $dailyAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        $completedAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->where('status', Appointment::STATUS_COMPLETED)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(8)
            ->get();

        $cancelledAppointments = (clone $appointments)
            ->with(['client', 'staff'])
            ->where('status', Appointment::STATUS_CANCELLED)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->limit(8)
            ->get();

        $clientVisitSummary = (clone $appointments)
            ->select([
                'appointments.client_id',
                DB::raw('COUNT(*) as total_visits'),
                DB::raw("SUM(CASE WHEN appointments.status = '" . Appointment::STATUS_COMPLETED . "' THEN 1 ELSE 0 END) as completed_visits"),
                DB::raw("SUM(CASE WHEN appointments.status = '" . Appointment::STATUS_CANCELLED . "' THEN 1 ELSE 0 END) as cancelled_visits"),
                DB::raw('MAX(appointments.appointment_date) as latest_visit_date'),
            ])
            ->join('clients', 'clients.id', '=', 'appointments.client_id')
            ->addSelect([
                'clients.first_name',
                'clients.last_name',
                'clients.phone',
            ])
            ->groupBy('appointments.client_id', 'clients.first_name', 'clients.last_name', 'clients.phone')
            ->orderByDesc('total_visits')
            ->orderBy('clients.last_name')
            ->limit(8)
            ->get()
            ->map(function ($clientSummary) {
                $clientSummary->full_name = trim($clientSummary->first_name . ' ' . $clientSummary->last_name);

                return $clientSummary;
            });

        return view('reports.index', compact(
            'reports',
            'staffActivity',
            'monthlyTrend',
            'statusDistribution',
            'dailyAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'clientVisitSummary',
        ));
    }
}
