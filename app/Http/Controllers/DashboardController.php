<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ServiceRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        Appointment::markReadyForNoShow();

        $user = request()->user();

        $appointments = Appointment::query()
            ->with(['client', 'staff'])
            ->visibleTo($user);

        $stats = [
            'clients' => $user->isStaff()
                ? (clone $appointments)->distinct('client_id')->count('client_id')
                : Client::count(),
            'appointments_today' => (clone $appointments)->whereDate('appointment_date', today())->count(),
            'completed_appointments' => (clone $appointments)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'service_records' => ServiceRecord::query()
                ->when($user->isStaff(), fn ($query) => $query->where('staff_id', $user->id))
                ->count(),
        ];

        $upcomingAppointments = (clone $appointments)
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get();

        $recentActivity = ServiceRecord::query()
            ->with(['client', 'staff', 'appointment'])
            ->when($user->isStaff(), fn ($query) => $query->where('staff_id', $user->id))
            ->latest('service_date')
            ->limit(5)
            ->get();

        $statusBreakdown = (clone $appointments)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard', compact('stats', 'upcomingAppointments', 'recentActivity', 'statusBreakdown'));
    }
}
