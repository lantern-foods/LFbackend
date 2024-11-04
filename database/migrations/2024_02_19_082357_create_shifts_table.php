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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cook_id');
            $table->decimal('estimated_revenue', 15, 2)->nullable()->default(0.00)->comment = "Estimated revenue for the shift"; // Allows null with default
            $table->time('start_time')->nullable()->comment = "Shift start time"; // Nullable to handle cases where it's not defined initially
            $table->time('end_time')->nullable()->comment = "Shift end time";   // Nullable to handle cases where it's not defined initially
            $table->date('shift_date')->comment = "Date of the shift";
            $table->tinyInteger('shift_status')->default(0)->comment = "0-scheduled shift, 1-active shift, 2-closed shift"; // Default to scheduled shift
            $table->timestamps();

            // Foreign key constraint with cascade delete to remove associated shifts if a cook is deleted
            $table->foreign('cook_id')->references('id')->on('cooks')->onDelete('cascade');

            // Index on cook_id for better performance when filtering by cook
            $table->index('cook_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
