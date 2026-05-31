<?php

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
        Schema::table('fuel_entries', function (Blueprint $table) {
            $table->decimal('dist_since_last_refuel', 8, 2)->nullable()->after('odometer_reading');
            $table->decimal('avg_kmpl', 8, 2)->nullable()->after('dist_since_last_refuel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_entries', function (Blueprint $table) {
            $table->dropColumn(['dist_since_last_refuel', 'avg_kmpl']);
        });
    }
};
