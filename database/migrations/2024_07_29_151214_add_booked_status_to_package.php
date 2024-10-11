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
        Schema::table('packages', function (Blueprint $table) {
            // Adding express_status and booked_status with default values
            $table->integer('express_status')->default(0);
            $table->integer('booked_status')->default(1);

            // Adding min_qty, max_qty, prep_time, and serving_advice if they do not exist
            if (!Schema::hasColumn('packages', 'min_qty')) {
                $table->integer('min_qty')->nullable()->comment('Minimum quantity for order');
                $table->integer('max_qty')->nullable()->comment('Maximum quantity for order');
                $table->string('prep_time')->nullable()->default('instant');
                $table->string('serving_advice')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Dropping the added columns in the reverse migration
            $table->dropColumn('express_status');
            $table->dropColumn('booked_status');
            $table->dropColumn('min_qty');
            $table->dropColumn('max_qty');
            $table->dropColumn('prep_time');
            $table->dropColumn('serving_advice');
        });
    }
};
