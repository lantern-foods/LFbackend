<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_admin_controls', function (Blueprint $table) {
            $table->id();
            $table->time('shift_start_time')->default('6:00:00');
            $table->time('shift_end_time')->default('10:00:00');
            $table->boolean('all_shifts_closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_admin_controls');
    }
};
