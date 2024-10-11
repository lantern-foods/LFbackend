<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            if (Schema::hasColumn('cart', 'meal_id')) {
                // Drop the foreign key constraint if it exists
                $table->dropForeign(['meal_id']);

                // Make the meal_id column nullable
                $table->unsignedBigInteger('meal_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            // Revert the column back to non-nullable and re-apply the foreign key
            $table->unsignedBigInteger('meal_id')->nullable(false)->change();
            $table->foreign('meal_id')->references('id')->on('meals')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
