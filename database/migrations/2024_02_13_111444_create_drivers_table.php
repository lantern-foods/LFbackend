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
            $table->unsignedBigInteger('company_id');
            $table->string('driver_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('id_number')->unique(); // Ensured unique constraint for ID number
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('password')->nullable();
            $table->string('drive_otp')->nullable();
            $table->tinyInteger('driver_status')->default(1)->comment = "Driver status: Active=1, Inactive=0";
            $table->timestamps();

            // Foreign key to delivery_companies
            $table->foreign('company_id')->references('id')->on('delivery_companies')->onUpdate('cascade')->onDelete('cascade');

            // Indexing fields
            $table->index('company_id');
            $table->index('email');
            $table->index('id_number');
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
