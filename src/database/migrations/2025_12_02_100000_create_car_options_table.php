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
        Schema::create('car_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('car_car_option', function (Blueprint $table) {
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_option_id')->constrained()->cascadeOnDelete();
            $table->primary(['car_id', 'car_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_car_option');
        Schema::dropIfExists('car_options');
    }
};
