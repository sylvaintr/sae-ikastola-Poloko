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
        Schema::create('tache_historique', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('idTache')->index('tache_historique_idtache');
            $table->string('statut', 50);
            $table->string('titre', 200)->nullable();
            $table->string('urgence', 50)->nullable();
            $table->text('description')->nullable();
            $table->integer('modifie_par')->nullable()->comment('ID de l\'utilisateur qui a modifié');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache_historique');
    }
};

