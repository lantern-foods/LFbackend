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

            $table->integer('packaging')->nullable()->default(0);
            $table->integer('taste')->nullable()->default(0);
            $table->integer('service')->nullable()->default(0);

            $table->string('review', 400)->nullable();
            $table->timestamps();

            // Adding foreign key constraints for data integrity
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');

            // Adding indexes to improve query performance
            $table->index('meal_id');
            $table->index('user_id');
            $table->index('package_id');
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
