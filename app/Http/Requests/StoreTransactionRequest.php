<?php

namespace App\Http\Requests;

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
            'date' => ['required', 'date'],
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

            if (! $account || $account->user_id !== Auth::user()->id) {
                $validator->errors()->add('account_id', 'Account not found');
            }

            $category = Category::find($data['category_id']);
            if (! $category) {
                $validator->errors()->add('category_id', 'Category not found');
            }

            if ($category->type === TransactionTypeEnum::EXPENSE && $account->balance < $data['amount']) {
                $validator->errors()->add('amount', 'Insufficient balance');
            }

            Context::add('account', $account);
            Context::add('category', $category);
        });
    }
}
