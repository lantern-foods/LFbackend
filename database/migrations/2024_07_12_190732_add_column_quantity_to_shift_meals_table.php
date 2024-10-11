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
        Schema::table('shift_meals', function (Blueprint $table) {
            // Add 'quantity' column if it doesn't exist
            if (!Schema::hasColumn('shift_meals', 'quantity')) {
                $table->integer('quantity')->after('meal_id')->default(1);
            }

            // Add timestamps if they don't exist
            if (!Schema::hasColumns('shift_meals', ['created_at', 'updated_at'])) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_meals', function (Blueprint $table) {
            // Remove 'quantity' column if it exists
            if (Schema::hasColumn('shift_meals', 'quantity')) {
                $table->dropColumn('quantity');
            }

            // Remove timestamps
            $table->dropTimestamps();
        });
    }
};
