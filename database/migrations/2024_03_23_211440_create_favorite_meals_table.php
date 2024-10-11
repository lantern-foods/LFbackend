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
        if (!Schema::hasTable('favorite_meals')) {
            Schema::create('favorite_meals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('meal_id');
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_meals');
    }
};
