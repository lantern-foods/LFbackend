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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');

            $table->string('email_address')->unique();

            $table->string('password')->nullable();

            // add
            // $table->string('google_map_pin')->nullable()->change();
            // $table->string('whatsapp_number')->nullable()->change();
            // $table->string('physical_address')->nullable()->change();
            $table->string('client_otp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
