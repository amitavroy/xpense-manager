<?php

namespace App\Http\Requests;

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'account_id' => ['required'],
            'category_id' => ['required'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(TransactionSourceTypeEnum::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('type')) {
            $this->merge(['type' => TransactionSourceTypeEnum::NORMAL->value]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->safe()->only(['account_id', 'category_id', 'amount']);
            $account = Account::find($data['account_id']);

            // Account should be owned by the user
            if (! $account || $account->user_id !== Auth::user()->id) {
                $validator->errors()->add('account_id', 'Account not found');
            }

            // Category should exist
            $category = Category::find($data['category_id']);
            if (! $category) {
                $validator->errors()->add('category_id', 'Category not found');
            }

            // If the category is an expense and account is not a credit card, the account should have enough balance
            if ($category->type === TransactionTypeEnum::EXPENSE && $account->type !== AccountTypeEnum::CREDIT_CARD && $account->balance < $data['amount']) {
                $validator->errors()->add('amount', 'Insufficient balance');
            }

            // If the account is a credit card, the amount should be less than the credit limit
            if ($account->type === AccountTypeEnum::CREDIT_CARD && $account->credit_limit < $data['amount']) {
                $validator->errors()->add('amount', 'Amount exceeds credit limit');
            }

            Context::add('account', $account);
            Context::add('category', $category);
        });
    }
}
