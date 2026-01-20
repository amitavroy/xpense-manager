<?php

namespace App\Http\Requests;

use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('transaction')->user_id === Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => ['required'],
            'category_id' => ['required'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(TransactionSourceTypeEnum::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->safe()->all();
            $transaction = $this->route('transaction');

            // Load related models
            $newAccount = Account::find($data['account_id']);
            $newCategory = Category::find($data['category_id']);
            $oldAccount = $transaction->account;
            $oldCategory = $transaction->category;

            // Validate models exist
            if (! $newAccount) {
                $validator->errors()->add('account_id', 'Account not found');

                return;
            }

            if (! $newCategory) {
                $validator->errors()->add('category_id', 'Category not found');

                return;
            }

            $oldAmount = $transaction->amount;
            $newAmount = $data['amount'];

            // Case 1: Same account
            if ($newAccount->id === $oldAccount->id) {
                $this->validateSameAccount($validator, $oldAmount, $newAmount, $newAccount);
            }

            // Case 2: Different account
            else {
                $this->validateDifferentAccount($validator, $newAmount, $newAccount);
            }

            Context::add('old_account', $oldAccount);
            Context::add('new_account', $newAccount);
            Context::add('old_category', $oldCategory);
            Context::add('new_category', $newCategory);
            Context::add('old_amount', $oldAmount);
        });
    }

    private function validateSameAccount($validator, float $oldAmount, float $newAmount, Account $account)
    {
        $amountDifference = $newAmount - $oldAmount;

        if ($amountDifference > 0) {
            if ($account->balance < $amountDifference) {
                $validator->errors()
                    ->add('amount', 'Insufficient balance for increased amount.');
            }
        }
    }

    private function validateDifferentAccount($validator, float $newAmount, Account $newAccount)
    {
        if ($newAccount->balance < $newAmount) {
            $validator->errors()
                ->add('amount', 'New account has insufficient balance');
        }
    }
}
