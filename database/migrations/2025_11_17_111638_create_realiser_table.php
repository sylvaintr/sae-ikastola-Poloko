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
        Schema::create('realiser', function (Blueprint $table) {
            $table->integer('idUtilisateur');
            $table->integer('idTache')->index('idtache');
            $table->date('dateM')->nullable();
            $table->string('description', 100)->nullable();

            $table->primary(['idUtilisateur', 'idTache']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realiser');
    }
};
