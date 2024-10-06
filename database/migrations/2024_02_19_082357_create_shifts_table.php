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
            $table->decimal('estimated_revenue', 15, 2);
            $table->time('start_time');
            $table->time('end_time');
            $table->date('shift_date');
            $table->tinyInteger('shift_status')->default(1)->comment="1-active shift, 2-closed shift";
            $table->timestamps();

        // Foreign key constraint
        $table->foreign('cook_id')->references('id')->on('cooks')->onDelete('cascade');
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
