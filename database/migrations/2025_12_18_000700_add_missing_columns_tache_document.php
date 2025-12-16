<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la colonne urgence si elle manque (tache)
        if (!Schema::hasColumn('tache', 'urgence')) {
            Schema::table('tache', function (Blueprint $table) {
                $table->string('urgence', 15)->default('Moyenne')->after('type');
            });
        }

        // Ajouter la colonne idTache si elle manque (document)
        if (!Schema::hasColumn('document', 'idTache')) {
            Schema::table('document', function (Blueprint $table) {
                $table->integer('idTache')->nullable()->after('idDocument');
                $table->index('idTache', 'document_idtache');
            });
        }
    }

    public function down(): void
    {
        // On ne supprime rien en rollback pour éviter de casser des données existantes
        if (Schema::hasColumn('document', 'idTache')) {
            Schema::table('document', function (Blueprint $table) {
                $table->dropIndex('document_idtache');
                $table->dropColumn('idTache');
            });
        }

        if (Schema::hasColumn('tache', 'urgence')) {
            Schema::table('tache', function (Blueprint $table) {
                $table->dropColumn('urgence');
            });
        }
    }
};

