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
        Schema::create('demande_historique', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('idDemande')->index('demande_historique_iddemande');
            $table->string('statut', 50);
            $table->string('titre', 100)->nullable();
            $table->string('responsable', 100)->nullable();
            $table->decimal('depense', 10, 2)->nullable();
            $table->date('dateE')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_historique');
    }
};

