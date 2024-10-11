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
        Schema::table('cart', function (Blueprint $table) {
            if (!Schema::hasColumn('cart', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('meal_id');
                // Optionally, you can add a foreign key constraint if needed:
                // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            }
            if (!Schema::hasColumn('cart', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('meal_id');
                // Optionally, you can add a foreign key constraint if needed:
                // $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('meal_id');
                // Optionally, you can add a foreign key constraint if needed:
                // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            }
            if (!Schema::hasColumn('order_details', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('meal_id');
                // Optionally, you can add a foreign key constraint if needed:
                // $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            if (Schema::hasColumn('cart', 'shift_id')) {
                $table->dropColumn('shift_id');
            }
            if (Schema::hasColumn('cart', 'package_id')) {
                $table->dropColumn('package_id');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'shift_id')) {
                $table->dropColumn('shift_id');
            }
            if (Schema::hasColumn('order_details', 'package_id')) {
                $table->dropColumn('package_id');
            }
        });
    }
};
