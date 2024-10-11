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
        Schema::create('order_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('client_id');
            $table->tinyInteger('meal_rating');
            $table->tinyInteger('driver_rating');
            $table->timestamps();

            // Foreign key constraint for 'order_id'
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // Foreign key constraint for 'client_id' (assuming 'clients' table exists)
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_ratings');
    }
};
