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
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key for the client ID
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Optional, indexed user_id for the client owner
            $table->string('name'); // Name of the client application
            $table->string('secret', 100)->nullable(); // Secret key for the client (nullable for some clients)
            $table->string('provider')->nullable(); // Provider for user authentication (nullable)
            $table->text('redirect'); // Redirect URI after successful authentication
            $table->boolean('personal_access_client'); // Whether this client is for personal access tokens
            $table->boolean('password_client'); // Whether this client is for password grant tokens
            $table->boolean('revoked'); // Whether this client is revoked
            $table->timestamps(); // Timestamps for creation and updates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
};
