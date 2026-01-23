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
            $category = Category::find($data['category_id']);

            // Validate models exist and belong to user
            $this->validateAccountOwnership($validator, $account);
            $this->validateCategoryExists($validator, $category);

            // Early return if models are invalid
            if ($validator->errors()->hasAny(['account_id', 'category_id'])) {
                return;
            }

            // Validate business rules
            $this->validateAccountBalance($validator, $account, $category, $data['amount']);
            $this->validateCreditLimit($validator, $account, $data['amount']);

            // Store models in context for use in controller/action
            Context::add('account', $account);
            Context::add('category', $category);
        });
    }

    private function validateAccountOwnership($validator, ?Account $account): void
    {
        if (! $account || $account->user_id !== Auth::user()->id) {
            $validator->errors()->add('account_id', 'Account not found');
        }
    }

    private function validateCategoryExists($validator, ?Category $category): void
    {
        if (! $category) {
            $validator->errors()->add('category_id', 'Category not found');
        }
    }

    private function validateAccountBalance($validator, Account $account, Category $category, float $amount): void
    {
        $isExpense = $category->type === TransactionTypeEnum::EXPENSE;
        $isNotCreditCard = $account->type !== AccountTypeEnum::CREDIT_CARD;

        if ($isExpense && $isNotCreditCard && $account->balance < $amount) {
            $validator->errors()->add('amount', 'Insufficient balance');
        }
    }

    private function validateCreditLimit($validator, Account $account, float $amount): void
    {
        if ($account->type === AccountTypeEnum::CREDIT_CARD && $account->credit_limit < $amount) {
            $validator->errors()->add('amount', 'Amount exceeds credit limit');
        }
    }
}
