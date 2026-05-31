<?php

use App\Models\FuelEntry;

test('computeMetrics returns null for both fields when there is no previous entry', function () {
    $metrics = FuelEntry::computeMetrics(null, 50000, 45.5);

    expect($metrics)->toBe([
        'dist_since_last_refuel' => null,
        'avg_kmpl' => null,
    ]);
});

test('computeMetrics calculates distance and average kmpl when a previous entry exists', function () {
    $previousEntry = new FuelEntry(['odometer_reading' => 49000]);

    $metrics = FuelEntry::computeMetrics($previousEntry, 50000, 40.0);

    expect($metrics)->toBe([
        'dist_since_last_refuel' => 1000,
        'avg_kmpl' => 25.0,
    ]);
});

test('computeMetrics returns null for avg_kmpl when fuel quantity is zero', function () {
    $previousEntry = new FuelEntry(['odometer_reading' => 49000]);

    $metrics = FuelEntry::computeMetrics($previousEntry, 50000, 0.0);

    expect($metrics)->toBe([
        'dist_since_last_refuel' => 1000,
        'avg_kmpl' => null,
    ]);
});
