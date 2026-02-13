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
            $table->id();
            $table->integer('idDemande')->index('iddemande');
            $table->string('statut', 60);
            $table->string('titre', 60);
            $table->string('responsable', 60)->nullable();
            $table->decimal('depense', 10, 2)->nullable();
            $table->dateTime('dateE')->nullable();
            $table->string('description', 255)->nullable();
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
