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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliverycmpy_id');
            $table->string('license_plate')->unique(); // Added unique constraint
            $table->string('make');
            $table->string('model');
            $table->string('description')->nullable();
            $table->timestamps();

            // Add foreign key constraint for delivery companies
            $table->foreign('deliverycmpy_id')->references('id')->on('delivery_companies')->onUpdate('cascade')->onDelete('cascade');

            // Indexing
            $table->index('deliverycmpy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
