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
        Schema::create('cooks', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->string('kitchen_name');
            $table->integer('id_number');
            $table->string('mpesa_number')->comment="Mpesa phone number";
            $table->string('alt_phone_number')->comment="Alternative phone number";
            $table->string('health_number')->comment="Health cert number";
            $table->date('health_expiry_date')->comment="Health cert expiry date";
            $table->string('physical_address')->comment="Building, road name";
            $table->string('google_map_pin')->comment="Google maps pin";
            $table->string('shrt_desc');
            $table->tinyInteger('status')->default(2)->comment="Determines whether the cook has been been approved";
            $table->timestamps();

            
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
