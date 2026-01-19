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
        Schema::create('recette', function (Blueprint $table) {
            $table->integer('idRecette')->primary();
            $table->string('description', 100);
            $table->string('prix', 50);
            $table->string('quantite', 50);
            $table->string('type', 50)->default('recette');
            $table->id('idRecette');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recette');
    }
};
