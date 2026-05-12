<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAppointmentSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    use ValidatesAppointmentSchedule;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'staff_id' => ['required', 'exists:users,id'],
            'service_type' => ['required', 'in:'.implode(',', \App\Models\Appointment::SERVICE_TYPES)],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
