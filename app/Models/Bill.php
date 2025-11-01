<?php

namespace App\Models;

use App\Enums\BillFrequencyEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    /** @use HasFactory<\Database\Factories\BillFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'biller_id',
        'default_amount',
        'frequency',
        'interval_days',
        'next_payment_date',
        'is_active',
        'auto_generate_bill',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'next_payment_date' => 'date',
        'is_active' => 'boolean',
        'auto_generate_bill' => 'boolean',
        'frequency' => BillFrequencyEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutoGenerateBill($query)
    {
        return $query->where('auto_generate_bill', true);
    }
}
