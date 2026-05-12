<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRecordRequest;
use App\Http\Requests\UpdateServiceRecordRequest;
use App\Models\Appointment;
use App\Models\ServiceRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceRecordController extends Controller
{
    public function index(Request $request): View
    {
        Appointment::markReadyForNoShow();

        $user = $request->user();
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        $serviceRecords = ServiceRecord::query()
            ->with(['appointment', 'client', 'staff'])
            ->when($user->isStaff(), fn (Builder $query) => $query->where('staff_id', $user->id))
            ->when($status !== '', fn (Builder $query) => $query->whereHas('appointment', fn (Builder $appointmentQuery) => $appointmentQuery->where('status', $status)))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('appointment_id', 'like', "%{$search}%")
                        ->orWhere('client_id', 'like', "%{$search}%")
                        ->orWhere('staff_id', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('staff', fn (Builder $staffQuery) => $staffQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('service_date')
            ->paginate(8)
            ->withQueryString();

        $availableAppointments = Appointment::query()
            ->with(['client', 'staff'])
            ->visibleTo($user)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->doesntHave('serviceRecord')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $completedAppointments = Appointment::query()
            ->with(['client', 'staff', 'serviceRecord'])
            ->visibleTo($user)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(8, ['*'], 'completed_page')
            ->withQueryString();

        $recordStats = [
            'completed_appointments' => Appointment::query()
                ->visibleTo($user)
                ->where('status', Appointment::STATUS_COMPLETED)
                ->count(),
            'with_records' => Appointment::query()
                ->visibleTo($user)
                ->where('status', Appointment::STATUS_COMPLETED)
                ->has('serviceRecord')
                ->count(),
            'pending_records' => Appointment::query()
                ->visibleTo($user)
                ->where('status', Appointment::STATUS_COMPLETED)
                ->doesntHave('serviceRecord')
                ->count(),
        ];

        return view('service-records.index', [
            'serviceRecords' => $serviceRecords,
            'availableAppointments' => $availableAppointments,
            'completedAppointments' => $completedAppointments,
            'recordStats' => $recordStats,
            'search' => $search,
            'status' => $status,
            'statuses' => Appointment::STATUSES,
        ]);
    }

    public function store(StoreServiceRecordRequest $request): RedirectResponse
    {
        Appointment::markReadyForNoShow();

        $appointment = Appointment::query()
            ->with('serviceRecord')
            ->findOrFail($request->integer('appointment_id'));

        $this->authorizeAppointment($appointment);
        abort_if($appointment->status !== Appointment::STATUS_COMPLETED, 422, 'Only completed appointments can have service records.');
        abort_if($appointment->serviceRecord()->exists(), 422, 'This appointment already has a service record.');

        ServiceRecord::create([
            'appointment_id' => $appointment->id,
            'client_id' => $appointment->client_id,
            'staff_id' => $appointment->staff_id,
            'description' => $request->validated('description'),
            'service_date' => $request->validated('service_date'),
            'remarks' => $request->validated('remarks'),
        ]);

        return redirect()
            ->route('service-records.index')
            ->with('status', 'Service record added successfully.');
    }

    public function edit(ServiceRecord $serviceRecord): View
    {
        $this->authorizeServiceRecord($serviceRecord);

        $serviceRecord->load(['appointment.client', 'appointment.staff', 'client', 'staff']);

        return view('service-records.edit', compact('serviceRecord'));
    }

    public function update(UpdateServiceRecordRequest $request, ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->authorizeServiceRecord($serviceRecord);

        $serviceRecord->update($request->validated());

        return redirect()
            ->route('service-records.index')
            ->with('status', 'Service record updated successfully.');
    }

    public function destroy(ServiceRecord $serviceRecord): RedirectResponse
    {
        $this->authorizeServiceRecord($serviceRecord);
        $serviceRecord->delete();

        return redirect()
            ->route('service-records.index')
            ->with('status', 'Service record deleted successfully.');
    }

    protected function authorizeAppointment(Appointment $appointment): void
    {
        $user = request()->user();

        abort_if($user->isStaff() && $appointment->staff_id !== $user->id, 403);
    }

    protected function authorizeServiceRecord(ServiceRecord $serviceRecord): void
    {
        $user = request()->user();

        abort_if($user->isStaff() && $serviceRecord->staff_id !== $user->id, 403);
    }
}
