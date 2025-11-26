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
        Schema::create('inclure', function (Blueprint $table) {
            $table->integer('idEvenement');
            $table->integer('idMateriel')->index('idmateriel');

            $table->primary(['idEvenement', 'idMateriel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inclure');
    }
};
