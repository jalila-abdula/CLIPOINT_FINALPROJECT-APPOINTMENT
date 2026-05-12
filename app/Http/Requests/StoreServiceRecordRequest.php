<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRecordRequest extends FormRequest
{
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
        $serviceRecordId = $this->route('serviceRecord')?->id;

        return [
            'appointment_id' => [
                'required',
                'exists:appointments,id',
                Rule::unique('service_records', 'appointment_id')->ignore($serviceRecordId),
            ],
            'description' => ['required', 'string', 'max:1000'],
            'service_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
