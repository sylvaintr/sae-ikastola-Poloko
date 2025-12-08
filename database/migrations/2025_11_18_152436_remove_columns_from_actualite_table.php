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
        Schema::table('actualite', function (Blueprint $table) {
            $table->dropIndex('iddocument');
            $table->dropIndex('idEtiquette');
            $table->dropColumn('idDocument');
            $table->dropColumn('idEtiquette');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actualite', function (Blueprint $table) {
            $table->bigInteger('idDocument')->nullable()->index('iddocument');
            $table->integer('idEtiquette')->nullable()->index('idetiquette');
        });
    }
};
