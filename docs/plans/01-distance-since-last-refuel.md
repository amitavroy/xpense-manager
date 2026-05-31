# Plan: Distance Since Last Refuel & Per Fill-up Avg (km/L)

## Context

The vehicle detail page shows a fuel entries table but gives no sense of how far the vehicle travelled between fill-ups or what mileage it achieved. This plan adds:

- `dist_since_last_refuel` (nullable decimal) on each `FuelEntry` — calculated at entry-creation time
- `avg_kmpl` (nullable decimal) on each `FuelEntry` — per fill-up efficiency (`dist / fuel_quantity`), stored at entry-creation time
- Both new columns displayed in the `FuelEntriesTable`
- A backfill console command for historical entries

No changes to the `Vehicle` model.

---

## Implementation Steps

### 1. Migration — add two columns to `fuel_entries`

Create a new migration:

```
php artisan make:migration add_dist_and_avg_to_fuel_entries_table
```

```php
$table->decimal('dist_since_last_refuel', 8, 2)->nullable()->after('odometer_reading');
$table->decimal('avg_kmpl', 8, 2)->nullable()->after('dist_since_last_refuel');
```

---

### 2. Update `FuelEntry` model

File: `app/Models/FuelEntry.php`

- Do **not** add these fields to `$fillable` — they are computed internally, never supplied by the user
- Add to `casts()` only:
  ```php
  'dist_since_last_refuel' => 'decimal:2',
  'avg_kmpl' => 'decimal:2',
  ```
- Add a static helper so the action and backfill command share one definition of the calculation:

  ```php
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
  ```

---

### 3. Update `AddFuelEntryAction::execute()`

File: `app/Actions/AddFuelEntryAction.php`

Query the most recent previous entry, compute metrics (defaults to `null` when none exists), then persist in a **single** `save()` — no conditional second write:

```php
$previousEntry = FuelEntry::where('vehicle_id', $data['vehicle_id'])
    ->where('user_id', $user->id)
    ->orderBy('odometer_reading', 'desc')
    ->first();

$metrics = FuelEntry::computeMetrics(
    $previousEntry,
    $data['odometer_reading'],
    $data['fuel_quantity'],
);

$fuelEntry = new FuelEntry($data);
$fuelEntry->forceFill($metrics);
$fuelEntry->save();
```

The computed fields are set via `forceFill()` (not `$fillable`) since they are never user-supplied. Runs inside the existing `DB::transaction()`, so no additional wrapping needed.

Replace the existing `$fuelEntry = FuelEntry::create($data);` line with the block above.

---

### 4. Console Command — backfill historical entries

File to create: `app/Console/Commands/CalculateAvgFuelConsumption.php`

Signature: `once:calculate-avg-fuel-consumption`

Logic:
- Get all distinct `vehicle_id` values from `fuel_entries`
- For each vehicle, fetch entries ordered by `odometer_reading` ascending
- Loop with a `$previousEntry` tracker: call `FuelEntry::computeMetrics($previousEntry, $entry->odometer_reading, $entry->fuel_quantity)`, `forceFill()` the result, `$entry->save()`, then set `$previousEntry = $entry`

Follow the naming/structure pattern of `app/Console/Commands/GeneratePendingBillInstance.php` (logic stays in `handle()`, no separate Action class needed).

---

### 5. TypeScript type update

File: `resources/js/types/index.d.ts`

Add to `FuelEntry` interface:

```ts
dist_since_last_refuel?: number | null;
avg_kmpl?: number | null;
```

---

### 6. Update `FuelEntriesTable`

File: `resources/js/tables/fuel-entries-table.tsx`

Add two new `<TableHead>` columns:
- **Distance (km)**
- **Avg (km/L)**

In each row, after existing columns, add two `<TableCell>` entries reading the stored values directly:

```tsx
<TableCell>
  {entry.dist_since_last_refuel != null
    ? Number(entry.dist_since_last_refuel).toLocaleString()
    : '-'}
</TableCell>

<TableCell>
  {entry.avg_kmpl != null
    ? Number(entry.avg_kmpl).toFixed(2)
    : '-'}
</TableCell>
```

---

### 7. Tests

**`tests/Unit/Models/FuelEntryTest.php`** — unit tests for `FuelEntry::computeMetrics()`:

1. Returns `null` for both fields when `$previousEntry` is `null` (first-ever entry)
2. Calculates `dist_since_last_refuel` and `avg_kmpl` correctly when a previous entry exists
3. Returns `null` for `avg_kmpl` when `fuel_quantity` is zero (division-by-zero guard)

**`tests/Feature/Actions/AddFuelEntryActionTest.php`** — integration tests, follow existing patterns in that file:

1. `dist_since_last_refuel` is `null` when no previous fuel entry exists for the vehicle+user
2. `avg_kmpl` is `null` when no previous fuel entry exists
3. `dist_since_last_refuel` is persisted correctly when a previous entry exists
4. `avg_kmpl` is persisted correctly when a previous entry exists
5. A previous entry belonging to a **different user** is ignored (`user_id` scope)

---

## Verification

```bash
# Run migration
php artisan migrate

# Run model + action tests
php artisan test --filter=FuelEntry
php artisan test --filter=AddFuelEntryAction

# Backfill historical entries
php artisan once:calculate-avg-fuel-consumption

# Start dev server and check /vehicles/{id}
composer run dev
```

Manually verify on the vehicle detail page:
- New fuel entries show correct Distance and Avg (km/L) values
- First-ever entry for a vehicle shows '-' in both columns
- Historical entries (after backfill) show correct values
