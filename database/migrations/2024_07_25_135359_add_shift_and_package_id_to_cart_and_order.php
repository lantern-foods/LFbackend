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
            if (!Schema::hasColumn('cart', 'shift_id') || !Schema::hasColumn('cart', 'package_id')) {
                $table->string('shift_id')->nullable();
                $table->string('package_id')->nullable();
                //
            }
        });
        Schema::table('order_details', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('order_details', 'shift_id') || !Schema::hasColumn('order_details', 'package_id')) {
                $table->string('shift_id')->nullable();
                $table->string('package_id')->nullable();
                //
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            //
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
                //
            }
        });
    }
};
