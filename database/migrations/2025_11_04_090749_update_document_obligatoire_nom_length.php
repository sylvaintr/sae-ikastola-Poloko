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
                if (Schema::hasColumn('documentObligatoire', 'nom')) {
                    $table->string('nom', 100)->nullable()->change();
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
                if (Schema::hasColumn('documentObligatoire', 'nom')) {
                    $table->string('nom', 20)->nullable()->change();
                }
            });
        }
    }
};

