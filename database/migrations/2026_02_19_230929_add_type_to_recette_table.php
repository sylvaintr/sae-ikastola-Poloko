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
        Schema::table('recette', function (Blueprint $table) {
            $table->enum('type', ['recette', 'depense', 'depense_previsionnelle'])
                  ->default('recette')
                  ->after('quantite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recette', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
