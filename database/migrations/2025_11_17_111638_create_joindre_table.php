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
        Schema::create('joindre', function (Blueprint $table) {
            $table->integer('idDocument');
            $table->integer('idActualite')->index('idactualite');

            $table->primary(['idDocument', 'idActualite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joindre');
    }
};
