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
            $table->id(); // Primary key
            $table->string('full_name'); // Client's full name
            $table->string('phone_number'); // Phone number
            $table->string('email_address')->unique(); // Email address must be unique
            $table->string('password')->nullable(); // Password can be nullable (for social logins or similar cases)

            // Additional fields that are optional (nullable)
            $table->string('google_map_pin')->nullable(); // Optional Google map location
            $table->string('whatsapp_number')->nullable(); // Optional WhatsApp number
            $table->string('physical_address')->nullable(); // Optional physical address

            $table->string('client_otp')->nullable(); // OTP for verification
            $table->timestamps(); // Laravel's created_at and updated_at
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
