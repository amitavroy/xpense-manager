<?php

use App\Models\Trip;
use App\Models\User;
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
        Schema::create('trip_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trip::class)->index();
            $table->foreignIdFor(User::class, 'paid_by')->index();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->boolean('is_shared')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_expenses');
    }
};
