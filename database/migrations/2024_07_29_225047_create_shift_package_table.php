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
        // Check if the table 'shift_packages' doesn't already exist
        if (!Schema::hasTable('shift_packages')) {
            Schema::create('shift_packages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('package_id'); // Use unsignedBigInteger for IDs
                $table->unsignedBigInteger('shift_id');
                $table->integer('quantity')->default(1); // Integer for quantity

                $table->tinyInteger('package_status')->default(0); // TinyInteger for status with default 0
                $table->timestamps();

                // If you want to add foreign key constraints, uncomment the following lines:
                // $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
                // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the table if it exists
        Schema::dropIfExists('shift_packages');
    }
};
