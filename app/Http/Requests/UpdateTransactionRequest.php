<?php

namespace App\Http\Requests;

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
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
            'date' => ['required', 'date', 'before_or_equal:today'],
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

            // Validate transaction type immutability
            $this->validateTransactionTypeImmutability($validator, $transaction, $oldAccount, $newAccount);

            // Case 1: Same account
            if ($newAccount->id === $oldAccount->id) {
                $this->validateSameAccount($validator, $transaction, $oldAmount, $newAmount, $newAccount);
            }

            // Case 2: Different account
            else {
                $this->validateDifferentAccount($validator, $transaction, $newAmount, $newAccount);
            }

            Context::add('old_account', $oldAccount);
            Context::add('new_account', $newAccount);
            Context::add('old_category', $oldCategory);
            Context::add('new_category', $newCategory);
            Context::add('old_amount', $oldAmount);
        });
    }

    private function validateTransactionTypeImmutability($validator, Transaction $transaction, Account $oldAccount, Account $newAccount): void
    {
        $isCreditCardTransaction = $transaction->type === TransactionSourceTypeEnum::CREDIT_CARD;
        $isNewAccountCreditCard = $newAccount->type === AccountTypeEnum::CREDIT_CARD;
        $accountChanged = $oldAccount->id !== $newAccount->id;

        // Credit card transaction can only change to another credit card account
        if ($isCreditCardTransaction && $accountChanged && ! $isNewAccountCreditCard) {
            $validator->errors()
                ->add('account_id', 'Credit card transactions can only be moved to another credit card account.');
        }

        // Normal transaction cannot be changed to a credit card transaction
        if ($transaction->type === TransactionSourceTypeEnum::NORMAL && $isNewAccountCreditCard) {
            $validator->errors()
                ->add('account_id', 'Transaction type cannot be changed.');
        }
    }

    private function validateSameAccount($validator, Transaction $transaction, float $oldAmount, float $newAmount, Account $account): void
    {
        $isCreditCardTransaction = $transaction->type === TransactionSourceTypeEnum::CREDIT_CARD;

        if ($isCreditCardTransaction) {
            // For credit card transactions, validate credit limit
            $amountDifference = $newAmount - $oldAmount;

            if ($amountDifference > 0) {
                // When increasing amount, check if credit_limit can accommodate the increase
                if ($account->credit_limit < $amountDifference) {
                    $validator->errors()
                        ->add('amount', 'Insufficient credit limit for increased amount.');
                }
            }
        } else {
            // For normal transactions, validate balance
            $amountDifference = $newAmount - $oldAmount;

            if ($amountDifference > 0) {
                if ($account->balance < $amountDifference) {
                    $validator->errors()
                        ->add('amount', 'Insufficient balance for increased amount.');
                }
            }
        }
    }

    private function validateDifferentAccount($validator, Transaction $transaction, float $newAmount, Account $newAccount): void
    {
        $isCreditCardTransaction = $transaction->type === TransactionSourceTypeEnum::CREDIT_CARD;

        if ($isCreditCardTransaction) {
            // For credit card transactions, validate credit limit
            if ($newAccount->credit_limit < $newAmount) {
                $validator->errors()
                    ->add('amount', 'Amount exceeds credit limit of the new account.');
            }
        } else {
            // For normal transactions, validate balance
            if ($newAccount->balance < $newAmount) {
                $validator->errors()
                    ->add('amount', 'New account has insufficient balance');
            }
        }
    }
}
