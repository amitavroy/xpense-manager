<?php

use App\Models\Account;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->index();
            $table->foreignIdFor(Vehicle::class)->index();
            $table->foreignIdFor(Account::class)->index();
            $table->date('date');
            $table->unsignedInteger('odometer_reading');
            $table->decimal('fuel_quantity', 8, 2);
            $table->decimal('amount', 10, 2);
            $table->string('petrol_station_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_entries');
    }
};
