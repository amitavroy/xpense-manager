<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelEntry extends Model
{
    /** @use HasFactory<\Database\Factories\FuelEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'account_id',
        'date',
        'odometer_reading',
        'fuel_quantity',
        'amount',
        'petrol_station_name',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'odometer_reading' => 'integer',
            'fuel_quantity' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
