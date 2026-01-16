<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TripExpense extends Model
{
    /** @use HasFactory<\Database\Factories\TripExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'paid_by',
        'date',
        'amount',
        'description',
        'is_shared',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_shared' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trip_expense_user');
    }
}
