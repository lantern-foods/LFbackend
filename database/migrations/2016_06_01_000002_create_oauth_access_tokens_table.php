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
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary(); // Token ID as primary key
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Nullable user_id, index for faster queries
            $table->uuid('client_id'); // UUID for client ID (OAuth client)
            $table->string('name')->nullable(); // Optional token name
            $table->text('scopes')->nullable(); // Nullable scopes, if any
            $table->boolean('revoked'); // Whether the token has been revoked
            $table->timestamps(); // Automatically manage created_at and updated_at
            $table->dateTime('expires_at')->nullable(); // Expiration time for the token

            // Optional: Add foreign key constraints if necessary
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
};
