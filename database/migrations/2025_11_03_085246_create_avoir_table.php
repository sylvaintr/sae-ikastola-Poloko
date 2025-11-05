<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('avoir', function (Blueprint $table) {
            // The Spatie package expects a polymorphic pivot with a model_type column
            // and a model id column (we use idUtilisateur). Keep idRole to reference role.
            $table->integer('idUtilisateur');
            $table->integer('idRole');
            $table->string('model_type');

            // Définir la clé primaire composée (include model_type for uniqueness)
            $table->primary(['idUtilisateur', 'idRole', 'model_type']);

            // Ajouter les index pour optimiser les requêtes
            $table->index('idUtilisateur');
            $table->index('idRole');
            $table->index('model_type');

            // Contraintes de clé étrangère
            $table->foreign('idUtilisateur')->references('idUtilisateur')->on('utilisateur')->onDelete('cascade');
            $table->foreign('idRole')->references('idRole')->on('role')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avoir');
    }
};
