<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recette', function (Blueprint $table) {
            if (!Schema::hasColumn('recette', 'type')) {
                $table->string('type', 50)->default('recette')->after('quantite');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recette', function (Blueprint $table) {
            if (Schema::hasColumn('recette', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
