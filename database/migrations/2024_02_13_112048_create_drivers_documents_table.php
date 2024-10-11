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
            $table->unsignedBigInteger('driver_id');
            $table->string('profile_pic')->comment = "Image of the Profile pic of the driver";
            $table->string('id_front')->comment = "Image of the Front face of the ID card";
            $table->string('id_back')->comment = "Image of the Back face of the ID card";
            $table->timestamps();

            // Foreign key to drivers
            $table->foreign('driver_id')->references('id')->on('drivers')->onUpdate('cascade')->onDelete('cascade');

            // Indexing driver_id
            $table->index('driver_id');
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
