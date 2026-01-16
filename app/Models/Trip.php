<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trip_user');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(TripExpense::class);
    }

    public function getTotalExpensesByUser(int $userId): float
    {
        return $this->expenses()
            ->where('paid_by', $userId)
            ->sum('amount') ?? 0.0;
    }

    public function getTotalSharedExpensesForUser(int $userId): float
    {
        return $this->expenses()
            ->where('is_shared', true)
            ->whereHas('sharedWith', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->sum('amount') ?? 0.0;
    }

    public function getTotalNonSharedExpensesForUser(int $userId): float
    {
        return $this->expenses()
            ->where('paid_by', $userId)
            ->where('is_shared', false)
            ->sum('amount') ?? 0.0;
    }

    public function isAccessibleBy(?int $userId = null): bool
    {
        $userId = $userId ?? \Illuminate\Support\Facades\Auth::id();

        return $this->user_id === $userId
            || $this->members()->where('users.id', $userId)->exists();
    }
}
