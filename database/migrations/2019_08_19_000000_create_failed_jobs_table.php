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
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('uuid')->unique(); // Unique identifier for each failed job
            $table->text('connection'); // The connection used by the job
            $table->text('queue'); // The queue the job was pushed to
            $table->longText('payload'); // The job payload that failed
            $table->longText('exception'); // The exception that caused the failure
            $table->timestamp('failed_at')->useCurrent(); // Timestamp when the job failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
