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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('driver_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('id_number');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('password')->nullable();
            $table->string('drive_otp')->nullable();
            $table->tinyInteger('driver_status')->default(1)->comment="Driver status either Active=1 or inactive 0";
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
