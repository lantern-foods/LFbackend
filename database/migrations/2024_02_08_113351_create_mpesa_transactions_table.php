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
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment="Order ID";
            $table->dateTime('request_date_time')->comment="Date/Time request was initiated";
            $table->string('merchant_request_id')->comment="Merchant Request ID";
            $table->string('checkout_request_id')->comment="Checkout Request ID";
            $table->string('response_code')->comment="Request's response code";
            $table->string('response_desc')->comment="Request's response description";
            $table->string('customer_msg')->comment="Request's customer message";
            $table->dateTime('response_date_time')->nullable()->comment="Date/Time payment response was received";
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->decimal('txn_amount', 10, 2)->nullable()->comment="Transaction Amount"; // Increased precision
            $table->string('mpesa_receipt_no')->nullable()->comment="MPESA Confirmation Code";
            $table->string('sender_phone_no')->nullable()->comment="Customer/Sender's phone number";
            $table->string('txn_status')->default('Open')->comment="Open/Closed/Failed/Cancelled - Transaction Status";
            $table->timestamps();

            // Foreign key with cascade to maintain integrity
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('cascade');

            // Add an index for better performance
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
