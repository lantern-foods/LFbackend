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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cook_id');
            $table->string('package_name');
            $table->string('package_description');
            $table->decimal('discount', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();

            // Foreign key to cooks table
            $table->foreign('cook_id')->references('id')->on('cooks')->onUpdate('cascade')->onDelete('cascade');

            // Indexing cook_id for better query performance
            $table->index('cook_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
