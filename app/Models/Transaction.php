<?php

namespace App\Models;

use App\Enums\TransactionSourceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'bill_instance_id',
        'amount',
        'date',
        'description',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'type' => TransactionSourceTypeEnum::class,
    ];

    protected $attributes = [
        'type' => 'normal',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function billInstance(): BelongsTo
    {
        return $this->belongsTo(BillInstance::class);
    }

    public function scopeNormal($query)
    {
        return $query->where('type', TransactionSourceTypeEnum::NORMAL);
    }

    public function scopeCreditCard($query)
    {
        return $query->where('type', TransactionSourceTypeEnum::CREDIT_CARD);
    }
}
