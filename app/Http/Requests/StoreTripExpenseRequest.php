<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTripExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get trip from route model binding
        $trip = $this->route('trip');

        if (! $trip) {
            return false;
        }

        // Check if user is a member of the trip
        return $trip->user_id === Auth::user()->id
            || $trip->members()->where('users.id', Auth::user()->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'exists:trips,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'is_shared' => ['boolean'],
            'shared_with' => ['array', 'required_if:is_shared,true'],
            'shared_with.*' => ['exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->safe()->only(['trip_id', 'shared_with', 'is_shared']);

            if (isset($data['trip_id'])) {
                $trip = Trip::find($data['trip_id']);

                if (! $trip) {
                    $validator->errors()->add('trip_id', 'Trip not found');

                    return;
                }

                // Authorization is handled in authorize() method

                // Validate shared_with users are trip members
                if ($data['is_shared'] && isset($data['shared_with']) && is_array($data['shared_with'])) {
                    $tripMemberIds = $trip->members()->pluck('users.id')->push($trip->user_id)->unique()->toArray();

                    foreach ($data['shared_with'] as $userId) {
                        if (! in_array($userId, $tripMemberIds)) {
                            $validator->errors()->add('shared_with', 'All shared users must be trip members');
                            break;
                        }
                    }
                }
            }
        });
    }
}
