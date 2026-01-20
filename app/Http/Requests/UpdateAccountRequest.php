<?php

namespace App\Http\Requests;

use App\Enums\AccountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('account')->user_id === Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AccountTypeEnum::class)],
            'balance' => ['required', 'numeric', 'min:0'],
            'credit_limit' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(
                    $this->input('type') === AccountTypeEnum::CREDIT_CARD->value
                ),
            ],
        ];
    }
}
