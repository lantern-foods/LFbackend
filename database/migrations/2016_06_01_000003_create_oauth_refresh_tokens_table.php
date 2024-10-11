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
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary(); // Primary key for refresh token ID
            $table->string('access_token_id', 100)->index(); // Indexed access_token_id to relate to oauth_access_tokens
            $table->boolean('revoked'); // Indicates if the refresh token is revoked
            $table->dateTime('expires_at')->nullable(); // Expiration timestamp of the refresh token

            // Optional: You can add a foreign key constraint to link to the access token
            // $table->foreign('access_token_id')->references('id')->on('oauth_access_tokens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_refresh_tokens');
    }
};
