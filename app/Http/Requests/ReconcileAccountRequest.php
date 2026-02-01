<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class ReconcileAccountRequest extends FormRequest
{
    private ?Account $resolvedAccount = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $account = $this->route('account');

        if (! $account instanceof Account) {
            $account = Account::find($account);
        }

        $this->resolvedAccount = $account;

        return $account && $account->user_id === Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'actual_balance' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        Context::add('account', $this->resolvedAccount);
    }
}
