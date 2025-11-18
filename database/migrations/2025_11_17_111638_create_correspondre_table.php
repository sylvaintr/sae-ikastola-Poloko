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
        Schema::create('correspondre', function (Blueprint $table) {
            $table->integer('idActualite');
            $table->integer('idEtiquette')->index('idetiquette');

            $table->primary(['idActualite', 'idEtiquette']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondre');
    }
};
