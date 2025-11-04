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
        Schema::create('actualite', function (Blueprint $table) {
            $table->integer('idActualite')->primary();
            $table->string('titre', 30)->nullable();
            $table->string('description', 100);
            $table->string('type', 20);
            $table->date('dateP');
            $table->boolean('archive');
            $table->string('lien', 2083)->nullable();
            $table->integer('idUtilisateur')->index('idutilisateur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualite');
    }
};
