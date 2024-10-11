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
        Schema::create('geofencing_areas', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8);   // Latitude of the center point of the geofencing area
            $table->decimal('longitude', 11, 8);  // Longitude of the center point of the geofencing area
            $table->decimal('radius', 8, 2);      // Radius of the geofence in meters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofencing_areas');
    }
};
