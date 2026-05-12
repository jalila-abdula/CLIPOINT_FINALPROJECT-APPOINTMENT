<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(): View
    {
        Appointment::markReadyForNoShow();

        $user = request()->user();
        $status = request('status');
        $search = request('search');

        $appointments = Appointment::query()
            ->with(['client', 'staff', 'creator', 'serviceRecord'])
            ->visibleTo($user)
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('staff', function ($staffQuery) use ($search) {
                        $staffQuery->where('name', 'like', "%{$search}%");
                    })->orWhere('service_type', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(10)
            ->withQueryString();

        return view('appointments.index', [
            'appointments' => $appointments,
            'search' => $search,
            'status' => $status,
            'statuses' => Appointment::STATUSES,
            'serviceTypes' => Appointment::SERVICE_TYPES,
            'clients' => Client::orderBy('first_name')->get(),
            'staffMembers' => User::query()->where('role', User::ROLE_STAFF)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Appointment::markReadyForNoShow();

        return view('appointments.create', [
            'appointment' => new Appointment(['status' => Appointment::STATUS_SCHEDULED]),
            'clients' => Client::orderBy('first_name')->get(),
            'staffMembers' => User::query()->where('role', User::ROLE_STAFF)->orderBy('name')->get(),
            'statuses' => Appointment::STATUSES,
            'serviceTypes' => Appointment::SERVICE_TYPES,
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        Appointment::create([
            ...$request->validated(),
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment): View
    {
        Appointment::markReadyForNoShow();
        $this->authorizeAppointment($appointment);

        $appointment->load(['client', 'staff', 'creator', 'serviceRecord.staff']);

        return view('appointments.show', [
            'appointment' => $appointment,
            'statuses' => Appointment::STATUSES,
            'serviceTypes' => Appointment::SERVICE_TYPES,
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        Appointment::markReadyForNoShow();
        $this->authorizeAppointment($appointment);

        return view('appointments.edit', [
            'appointment' => $appointment,
            'clients' => Client::orderBy('first_name')->get(),
            'staffMembers' => User::query()->where('role', User::ROLE_STAFF)->orderBy('name')->get(),
            'statuses' => Appointment::STATUSES,
            'serviceTypes' => Appointment::SERVICE_TYPES,
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);

        $appointment->update($request->validated());

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment updated successfully.');
    }

    public function updateStatus(Appointment $appointment): RedirectResponse
    {
        Appointment::markReadyForNoShow();
        $this->authorizeAppointment($appointment);

        $validated = request()->validate([
            'status' => ['required', 'in:'.implode(',', Appointment::STATUSES)],
        ]);

        $appointment->update($validated);

        return back()->with('status', 'Appointment status updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointment($appointment);
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment deleted successfully.');
    }

    public function showRecords(): View
    {
        $records = DB::select('CALL show_appointments()');

        return view('appointments.records', [
            'records' => $records,
        ]);
    }

    protected function authorizeAppointment(Appointment $appointment): void
    {
        $user = request()->user();

        abort_if($user->isStaff() && $appointment->staff_id !== $user->id, 403);
    }
}
