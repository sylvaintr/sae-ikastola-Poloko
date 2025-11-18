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
        if (!Schema::hasTable('documentObligatoire')) {
            return;
        }

        $columnsToDrop = $this->getColumnsToDrop();

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('documentObligatoire', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }

    /**
     * Get the list of columns to drop.
     */
    private function getColumnsToDrop(): array
    {
        $columns = [];
        $tableName = 'documentObligatoire';

        if (Schema::hasColumn($tableName, 'delai')) {
            $columns[] = 'delai';
        }

        if (Schema::hasColumn($tableName, 'dateExpiration')) {
            $columns[] = 'dateExpiration';
        }

        return $columns;
    }
};

