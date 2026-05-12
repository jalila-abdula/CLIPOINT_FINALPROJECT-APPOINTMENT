<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateClientRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address_house_street' => ['required', 'string', 'max:255'],
            'address_barangay' => ['required', 'string', 'max:255'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_postal_province' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $client = $this->route('client');

            $duplicateExists = Client::query()
                ->whereKeyNot($client?->id)
                ->whereRaw('LOWER(first_name) = ?', [mb_strtolower(trim((string) $this->input('first_name')))])
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower(trim((string) $this->input('last_name')))])
                ->where('phone', trim((string) $this->input('phone')))
                ->exists();

            if ($duplicateExists) {
                $validator->errors()->add('first_name', 'This client already exists.');
            }
        });
    }

    /**
     * Get the validated data from the request.
     * Merge address components into a single address field.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();

        // Merge address components into a single address field
        $validated['address'] = $validated['address_house_street'] . ', '
            . $validated['address_barangay'] . ', '
            . $validated['address_city'] . ', '
            . $validated['address_postal_province'];

        // Remove individual address component fields
        unset($validated['address_house_street']);
        unset($validated['address_barangay']);
        unset($validated['address_city']);
        unset($validated['address_postal_province']);

        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $validated;
    }
}
