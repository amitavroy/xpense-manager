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
        Schema::create('trip_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Trip::class)->index();
            $table->foreignIdFor(User::class)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_user');
    }
};
