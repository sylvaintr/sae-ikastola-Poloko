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
        if (Schema::hasTable('documentObligatoire')) {
            Schema::table('documentObligatoire', function (Blueprint $table) {
                if (!Schema::hasColumn('documentObligatoire', 'delai')) {
                    $table->integer('delai')->nullable()->after('dateE')->comment('Délai en jours après dépôt du fichier');
                }
                if (!Schema::hasColumn('documentObligatoire', 'dateExpiration')) {
                    $table->date('dateExpiration')->nullable()->after('delai')->comment('Date d\'expiration fixe');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('documentObligatoire')) {
            Schema::table('documentObligatoire', function (Blueprint $table) {
                if (Schema::hasColumn('documentObligatoire', 'delai') || Schema::hasColumn('documentObligatoire', 'dateExpiration')) {
                    $columns = [];
                    if (Schema::hasColumn('documentObligatoire', 'delai')) {
                        $columns[] = 'delai';
                    }
                    if (Schema::hasColumn('documentObligatoire', 'dateExpiration')) {
                        $columns[] = 'dateExpiration';
                    }
                    if (!empty($columns)) {
                        $table->dropColumn($columns);
                    }
                }
            });
        }
    }
};

