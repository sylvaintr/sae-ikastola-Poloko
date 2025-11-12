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
        Schema::table('documentObligatoire', function (Blueprint $table) {
            $table->integer('delai')->nullable()->after('dateE')->comment('Délai en jours après dépôt du fichier');
            $table->date('dateExpiration')->nullable()->after('delai')->comment('Date d\'expiration fixe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentObligatoire', function (Blueprint $table) {
            $table->dropColumn(['delai', 'dateExpiration']);
        });
    }
};

