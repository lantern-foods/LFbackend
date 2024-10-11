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
        Schema::create('cooks', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('client_id'); // Foreign key reference to client table
            $table->string('kitchen_name'); // Kitchen name
            $table->string('id_number'); // Cook's identification number
            $table->string('mpesa_number')->comment('Mpesa phone number'); // Mpesa phone number
            $table->string('alt_phone_number')->comment('Alternative phone number'); // Alternative phone number
            $table->string('health_number')->comment('Health certificate number'); // Health certificate number
            $table->date('health_expiry_date')->comment('Health certificate expiry date'); // Health certificate expiry date
            $table->string('physical_address')->comment('Building, road name'); // Physical address
            $table->string('google_map_pin')->comment('Google Maps pin'); // Google Maps pin
            $table->string('shrt_desc')->comment('Short description'); // Short description
            $table->tinyInteger('status')->default(2)->comment('Approval status of the cook'); // Cook approval status
            $table->timestamps(); // Created at and updated at timestamps

            // Indexes
            $table->index('client_id'); // Index for client_id to optimize queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooks');
    }
};
