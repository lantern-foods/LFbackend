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
        Schema::create('cooks_documents', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('cook_id'); // Foreign key for cook
            $table->string('id_front')->comment('Image of the front face of the ID card');
            $table->string('id_back')->comment('Image of the back face of the ID card');
            $table->string('health_cert')->comment('Image of the health certificate');
            $table->string('profile_pic')->comment('Profile picture of the cook');
            $table->timestamps(); // Created at and updated at timestamps

            // Add a foreign key constraint (optional but recommended)
            $table->foreign('cook_id')->references('id')->on('cooks')->onDelete('cascade');

            // Index for optimizing queries on cook_id
            $table->index('cook_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooks_documents');
    }
};
