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
        Schema::create('cooks_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('cook_id');
            $table->string('id_front')->comment="Image of the Front face of the ID card";
            $table->string('id_back')->comment="Image of the Back face of the ID card";
            $table->string('health_cert')->comment="Image of the health certificate";
            $table->string('profile_pic')->comment="Profile pic image of the cook";
            $table->timestamps();
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
