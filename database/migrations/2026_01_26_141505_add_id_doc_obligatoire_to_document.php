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
        Schema::table('document', function (Blueprint $table) {
            // Ajout de la colonne pour lier le document à son type obligatoire
            $table->integer('idDocumentObligatoire')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document', function (Blueprint $table) {
            // Suppression de la colonne en cas de retour en arrière
            $table->dropColumn('idDocumentObligatoire');
        });
    }
};