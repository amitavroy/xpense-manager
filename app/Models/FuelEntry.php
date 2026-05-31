<?php

namespace App\Models;

use Database\Factories\FuelEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelEntry extends Model
{
    /** @use HasFactory<FuelEntryFactory> */
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
            'dist_since_last_refuel' => 'decimal:2',
            'avg_kmpl' => 'decimal:2',
            'fuel_quantity' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return array{dist_since_last_refuel: int|null, avg_kmpl: float|null}
     */
    public static function computeMetrics(?self $previousEntry, int $odometerReading, float $fuelQuantity): array
    {
        if ($previousEntry === null) {
            return ['dist_since_last_refuel' => null, 'avg_kmpl' => null];
        }

        $dist = $odometerReading - $previousEntry->odometer_reading;

        return [
            'dist_since_last_refuel' => $dist,
            'avg_kmpl' => $fuelQuantity > 0 ? round($dist / $fuelQuantity, 2) : null,
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
