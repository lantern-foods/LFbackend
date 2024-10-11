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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->morphs('tokenable'); // Polymorphic relationship to allow tokens to belong to multiple models
            $table->string('name'); // Name of the token
            $table->string('token', 64)->unique(); // Unique token value
            $table->text('abilities')->nullable(); // Defines abilities or scopes for the token
            $table->timestamp('last_used_at')->nullable(); // When the token was last used
            $table->timestamp('expires_at')->nullable(); // Expiration time of the token
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
