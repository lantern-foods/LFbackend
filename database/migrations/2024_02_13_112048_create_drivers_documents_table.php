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
        Schema::create('drivers_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('driver_id');
            $table->string('profile_pic')->comment="Image of the Profile pic of the driver";
            $table->string('id_front')->comment="Image of the Front face of the ID card";
            $table->string('id_back')->comment="Image of the Back face of the ID card";
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers_documents');
    }
};
