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
        Schema::create('meal_package_ratings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('meal_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->integer('packaging')->nullable();
            $table->integer('taste')->nullable();

            $table->integer('service')->nullable();
            $table->string('review',400)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_package_ratings');
    }
};
