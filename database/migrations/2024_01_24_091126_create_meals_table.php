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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cook_id')->comment("Foreign key from the clients table (Cook is a Client)");
            $table->string('meal_name');
            $table->decimal('meal_price', 15, 2);
            $table->integer('min_qty')->comment("Minimum quantity for order");
            $table->integer('max_qty')->comment("Maximum quantity for order");
            $table->string('meal_type');
            $table->string('prep_time');
            $table->text('meal_desc');
            $table->text('ingredients');
            $table->text('serving_advice');
            $table->integer('booked_status')->default(1);
            $table->integer('express_status')->default(0);
            $table->integer('status')->default(0)->comment("0 indicates meal is not approved");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
