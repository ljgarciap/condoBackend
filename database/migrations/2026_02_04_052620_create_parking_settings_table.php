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
        Schema::create('parking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'car_capacity', 'motorcycle_capacity'
            $table->string('value'); // Store as string, cast later if needed
            $table->timestamps();
        });

        // Seed default values
        DB::table('parking_settings')->insert([
            ['key' => 'car_capacity', 'value' => '50', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'motorcycle_capacity', 'value' => '20', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_settings');
    }
};
