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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('order_no', 50)->unique(); // Limited to 50 characters
            $table->integer('order_type')->default(1)->comment="1-Express, 2-Booked";
            $table->string('dt_req')->nullable()->comment="Delivery Time Requested";
            $table->decimal('order_total', 10, 2); // Increased precision for larger orders
            $table->string('status', 20); // Limit status length to 20 characters
            $table->string('cook_dely_otp', 6)->comment="Cook Delivery OTP"; // Limited to 6 characters
            $table->string('client_dely_otp', 6)->comment="Client Delivery OTP"; // Limited to 6 characters
            $table->timestamps();

            // Foreign key with cascade options
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
