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
        Schema::table('cart', function (Blueprint $table) {
            if (Schema::hasColumn('cart', 'meal_id')) {
                // Check if the foreign key exists before dropping it
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreignKeys = $sm->listTableForeignKeys('cart');
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns() === ['meal_id']) {
                        $table->dropForeign(['meal_id']);
                    }
                }
                $table->unsignedBigInteger('meal_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            $table->foreign('meal_id')->references('id')->on('meals')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
