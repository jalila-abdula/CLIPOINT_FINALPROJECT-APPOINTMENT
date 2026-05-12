<?php

namespace App\Http\Requests\Concerns;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\Validator as LaravelValidator;

trait ValidatesAppointmentSchedule
{
    public function withValidator(LaravelValidator $validator): void
    {
        $validator->after(function (LaravelValidator $validator): void {
            if ($validator->errors()->hasAny([
                'client_id',
                'appointment_date',
                'appointment_time',
                'status',
            ])) {
                return;
            }

            $scheduledAt = $this->scheduledAt();

            if (! $scheduledAt) {
                return;
            }

            if ($this->shouldBlockPastSchedule() && $scheduledAt->lt(now(config('app.timezone')))) {
                $validator->errors()->add('appointment_time', 'Appointments can only be scheduled for the current time or later.');
            }

            if ($this->shouldSkipSameDayConflictCheck()) {
                return;
            }

            $duplicateAppointmentExists = Appointment::query()
                ->booked()
                ->where('client_id', $this->integer('client_id'))
                ->whereDate('appointment_date', $scheduledAt->toDateString())
                ->when($this->currentAppointment(), function ($query, Appointment $appointment) {
                    $query->whereKeyNot($appointment->getKey());
                })
                ->exists();

            if ($duplicateAppointmentExists) {
                $validator->errors()->add('appointment_date', 'This client already has an appointment on the selected day.');
            }
        });
    }

    protected function shouldBlockPastSchedule(): bool
    {
        return ! $this->currentAppointment() || $this->scheduleWasChanged();
    }

    protected function shouldSkipSameDayConflictCheck(): bool
    {
        return $this->input('status') === Appointment::STATUS_CANCELLED;
    }

    protected function scheduleWasChanged(): bool
    {
        $appointment = $this->currentAppointment();

        if (! $appointment) {
            return true;
        }

        return $this->input('appointment_date') !== $appointment->appointment_date?->format('Y-m-d')
            || $this->input('appointment_time') !== Carbon::parse($appointment->appointment_time)->format('H:i');
    }

    protected function currentAppointment(): ?Appointment
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment ? $appointment : null;
    }

    protected function scheduledAt(): ?Carbon
    {
        $appointmentDate = $this->input('appointment_date');
        $appointmentTime = $this->input('appointment_time');

        if (! $appointmentDate || ! $appointmentTime) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i', "{$appointmentDate} {$appointmentTime}", config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
