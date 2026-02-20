<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer temporairement la contrainte de clé étrangère
        Schema::table('evenement_role', function (Blueprint $table) {
            $table->dropForeign(['idEvenement']);
        });

        Schema::table('evenement', function (Blueprint $table) {
            // Rendre idEvenement auto-increment
            $table->integer('idEvenement', true)->change();

            // Agrandir titre et description
            $table->string('titre', 255)->change();
            $table->text('description')->change();

            // Ajouter start_at / end_at
            $table->datetime('start_at')->nullable()->after('obligatoire');
            $table->datetime('end_at')->nullable()->after('start_at');
        });

        // Migrer dateE vers start_at pour les données existantes
        DB::statement('UPDATE evenement SET start_at = dateE WHERE start_at IS NULL AND dateE IS NOT NULL');

        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn('dateE');
        });

        // Re-créer la contrainte de clé étrangère
        Schema::table('evenement_role', function (Blueprint $table) {
            $table->foreign('idEvenement')
                ->references('idEvenement')
                ->on('evenement')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Supprimer temporairement la contrainte de clé étrangère
        Schema::table('evenement_role', function (Blueprint $table) {
            $table->dropForeign(['idEvenement']);
        });

        Schema::table('evenement', function (Blueprint $table) {
            $table->date('dateE')->nullable()->after('obligatoire');
        });

        DB::statement('UPDATE evenement SET dateE = DATE(start_at) WHERE dateE IS NULL AND start_at IS NOT NULL');

        Schema::table('evenement', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at']);
            $table->string('titre', 20)->change();
            $table->string('description', 100)->change();
        });

        // Re-créer la contrainte de clé étrangère
        Schema::table('evenement_role', function (Blueprint $table) {
            $table->foreign('idEvenement')
                ->references('idEvenement')
                ->on('evenement')
                ->onDelete('cascade');
        });
    }
};
