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
            $table->id();
            $table->integer('idTache');
            $table->string('statut', 30);
            $table->string('titre', 50)->nullable();
            $table->string('responsable', 60)->nullable();
            $table->decimal('depense', 8, 2)->nullable();
            $table->date('date_evenement')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('idTache');
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

