<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DropdownService
{
    /**
     * Get all categories for the user
     *
     * @return Collection<int, Category>
     */
    public function getCategories(TransactionTypeEnum $type): Collection
    {
        return Category::query()
            ->select('id', 'name')
            ->where('type', $type)
            ->get();
    }

    /**
     * Get all accounts for the user
     *
     * @return Collection<int, Account>
     */
    public function getAccounts(User $user): Collection
    {
        return Account::query()
            ->select('id', 'name')
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Get all users for dropdown
     *
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}
