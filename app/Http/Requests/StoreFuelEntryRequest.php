<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class StoreFuelEntryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'date' => ['required', 'date'],
            'odometer_reading' => ['required', 'integer', 'min:0'],
            'fuel_quantity' => ['required', 'numeric', 'min:0'],
            'amount' => ['required', 'numeric', 'min:0'],
            'petrol_station_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->safe()->only(['vehicle_id', 'odometer_reading']);
            $vehicle = Vehicle::find($data['vehicle_id']);

            if ($vehicle && isset($data['odometer_reading'])) {
                if ($data['odometer_reading'] <= $vehicle->kilometers) {
                    $validator->errors()->add(
                        'odometer_reading',
                        'The odometer reading must be greater than the current vehicle kilometers ('.$vehicle->kilometers.').'
                    );
                }
            }
        });
    }
}
