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
            $table->bigIncrements('idRecette');
            $table->string('description', 100);
            $table->string('prix', 50);
            $table->string('quantite', 50);
            $table->integer('idEvenement')->index('idevenement');
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
