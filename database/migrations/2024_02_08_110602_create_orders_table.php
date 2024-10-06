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
            $table->string('order_no')->unique();
            $table->integer('order_type')->default(1)->comment="1-Express,2-Booked";
            $table->string('dt_req')->nullable()->comment="Delivery Time Requested";
            $table->decimal('order_total',8,2);
            $table->string('status');
            $table->string('cook_dely_otp')->comment="Cook Delivery OTP";
            $table->string('client_dely_otp')->comment="Client Delivery OTP";
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('restrict')->onDelete('restrict');
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
