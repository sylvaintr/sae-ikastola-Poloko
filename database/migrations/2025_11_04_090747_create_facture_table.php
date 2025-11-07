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
        Schema::create('facture', function (Blueprint $table) {
            $table->integer('idFacture')->primary()->autoIncrement();
            $table->boolean('etat');
            $table->date('dateC');
            $table->integer('idUtilisateur')->index('idutilisateur');
            $table->integer('idFamille')->index('idfamille');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture');
    }
};
