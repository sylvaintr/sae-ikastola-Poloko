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
        Schema::create('enfant', function (Blueprint $table) {
            $table->id('idEnfant');
            $table->string('nom', 20);
            $table->string('prenom', 150);
            $table->date('dateN');
            $table->string('sexe', 5);
            $table->integer('NNI');
            $table->integer('idClasse')->index('idclasse');
            $table->integer('idFamille')->index('idfamille');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfant');
    }
};
