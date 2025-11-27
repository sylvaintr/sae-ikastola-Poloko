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
        Schema::create('posseder', function (Blueprint $table) {
            $table->integer('idRole');
            $table->integer('idEtiquette');

            // Définir la clé primaire composée
            $table->primary(['idRole', 'idEtiquette']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posseder');
    }
};
