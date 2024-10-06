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
            //
            $table->integer('express_status')->default(0);
            $table->integer('booked_status')->default(1);
            if (!Schema::hasColumn('packages', 'min_qty')) {
                $table->integer('min_qty')->nullable()->comments = "Minimum quantity for order";
                $table->integer('max_qty')->nullable()->comments = "Maximum quantity for order";

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
            //
            $table->dropColumn('express_status');
            $table->dropColumn('booked_status');
            $table->dropColumn('min_qty');
            $table->dropColumn('max_qty');
            $table->dropColumn('prep_time');
            $table->dropColumn('serving_advice');
        });
    }
};
