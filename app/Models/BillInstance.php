<?php

namespace App\Models;

use App\Enums\BillStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillInstance extends Model
{
    /** @use HasFactory<\Database\Factories\BillInstanceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_id',
        'transaction_id',
        'due_date',
        'amount',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'status' => BillStatusEnum::class,
        'amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
