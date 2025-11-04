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
        Schema::create('avoir', function (Blueprint $table) {
            $table->integer('idUtilisateur');
            $table->integer('idRole')->index('idrole');

            $table->primary(['idUtilisateur', 'idRole']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avoir');
    }
};
