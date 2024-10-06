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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('meal_id');
            $table->integer('qty');
            $table->decimal('unit_price',8,2);
            $table->decimal('subtotal',8,2);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('meal_id')->references('id')->on('meals')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
