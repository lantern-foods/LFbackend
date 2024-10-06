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
            $table->integer('cook_id')->comments="This is Client it from the clients table";
            $table->string('meal_name');
            $table->decimal('meal_price',15,2);
            $table->integer('min_qty')->comments="Minimum quantity for order";
            $table->integer('max_qty')->comments="Maximum quantity for order";
            $table->string('meal_type');
            $table->string('prep_time');
            $table->string('meal_desc');
            $table->string('ingredients');
            $table->string('serving_advice');
            $table->integer('booked_status')->default(1);
            $table->integer('express_status')->default(0);
            $table->integer('status')->default(0)->comments="Checks if meal has been approved ie. 0 no approved";
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
