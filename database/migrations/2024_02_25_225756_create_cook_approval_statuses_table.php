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
        Schema::create('cook_approval_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cook_id');
            $table->boolean('kitchen_name_approved')->default(false);
            $table->boolean('id_number_approved')->default(false);
            $table->boolean('mpesa_number_approved')->default(false);
            $table->boolean('health_number_approved')->default(false);
            $table->boolean('health_expiry_date_approved')->default(false);
            $table->boolean('shrt_desc_approved')->default(false);
            $table->boolean('id_front_approved')->default(false);
            $table->boolean('id_back_approved')->default(false);
            $table->boolean('health_cert_approved')->default(false);
            $table->boolean('profile_pic_approved')->default(false);
            $table->boolean('approved')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Foreign key constraint for cook_id
            $table->foreign('cook_id')->references('id')->on('cooks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cook_approval_statuses');
    }
};
