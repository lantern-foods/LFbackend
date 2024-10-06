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
        Schema::table('shift_meals', function (Blueprint $table) {
            //
            if(!Schema::hasColumn('shift_meals', 'quantity')){
                $table->integer('quantity')->after('meal_id')->default(1);
            }
            if(!Schema::hasColumn('shift_meals', 'created_at')){

                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_meals', function (Blueprint $table) {
            //
            if(Schema::hasColumn('shift_meals', 'quantity')){
                $table->dropColumn('quantity');
            }
        });
    }
};
