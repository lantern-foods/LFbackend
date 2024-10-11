<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary key
            $table->string('queue')->index(); // Queue name, indexed for performance
            $table->longText('payload'); // The serialized job payload
            $table->unsignedTinyInteger('attempts'); // Number of attempts for the job
            $table->unsignedInteger('reserved_at')->nullable(); // Time when the job is reserved (nullable)
            $table->unsignedInteger('available_at'); // Time when the job is available for processing
            $table->unsignedInteger('created_at'); // Time when the job was created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
