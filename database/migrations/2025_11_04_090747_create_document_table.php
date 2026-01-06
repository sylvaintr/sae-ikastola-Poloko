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
        Schema::create('document', function (Blueprint $table) {
            $table->integer('idDocument')->primary()->autoIncrement();
            $table->integer('idTache')->nullable()->index('document_idtache');
            $table->string('nom', 50);
            $table->string('chemin', 100);
            $table->string('type', 5);
            $table->string('etat', 15);
            // Liaison optionnelle vers une tâche (si le schéma courant le permet)
            if (!Schema::hasColumn('document', 'idTache')) {
                $table->integer('idTache')->nullable()->index('document_idtache');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document');
    }
};
