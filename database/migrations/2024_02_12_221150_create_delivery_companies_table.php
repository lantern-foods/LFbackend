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
        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number')->unique(); // Added uniqueness constraint
            $table->string('email')->unique();
            $table->string('company')->nullable();
            $table->string('password')->nullable();
            $table->string('delvry_otp')->nullable();
            $table->decimal('location_charge', 8, 2)->nullable()->comment('Delivery location charge'); // Changed to decimal
            $table->timestamps();

            // Add indexes to frequently queried fields
            $table->index('email');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_companies');
    }
};
