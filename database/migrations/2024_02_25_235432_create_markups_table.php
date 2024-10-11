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
        Schema::create('markups', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');    // The date when the markup starts
            $table->date('end_date');      // The date when the markup ends
            $table->integer('mark_up');    // Markup percentage or value
            $table->integer('order_type'); // Type of order to apply the markup on
            $table->tinyInteger('status')->default(0); // 0 for inactive, 1 for active status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markups');
    }
};
