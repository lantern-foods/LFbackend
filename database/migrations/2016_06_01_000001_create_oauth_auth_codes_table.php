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
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary(); // Primary key for auth code ID
            $table->unsignedBigInteger('user_id')->index(); // Foreign key to users table
            $table->uuid('client_id'); // Use UUID for clients as it represents the client app
            $table->text('scopes')->nullable(); // Nullable since some tokens might not have scopes
            $table->boolean('revoked'); // To check if the auth code is revoked
            $table->dateTime('expires_at')->nullable(); // Expiry date for the auth code

            // Add foreign key constraints, if necessary:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_auth_codes');
    }
};
