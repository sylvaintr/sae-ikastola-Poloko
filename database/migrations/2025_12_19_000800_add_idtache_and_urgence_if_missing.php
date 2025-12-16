<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sécurise la colonne urgence sur tache si absente
        if (!Schema::hasColumn('tache', 'urgence')) {
            Schema::table('tache', function (Blueprint $table) {
                $table->string('urgence', 15)->default('Moyenne')->after('type');
            });
        }

        // Sécurise la colonne idTache sur document si absente
        if (!Schema::hasColumn('document', 'idTache')) {
            Schema::table('document', function (Blueprint $table) {
                $table->integer('idTache')->nullable()->after('idDocument');
                $table->index('idTache', 'document_idtache');
            });
        }
    }

    public function down(): void
    {
        // On ne retire pas les colonnes pour ne pas casser des données existantes
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

