<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tache_role', function (Blueprint $table) {
            $table->integer('idTache');
            $table->integer('idRole');

            $table->primary(['idTache', 'idRole']);

            $table->index('idTache');
            $table->index('idRole');

            $table->foreign('idTache')
                ->references('idTache')
                ->on('tache')
                ->onDelete('cascade');

            $table->foreign('idRole')
                ->references('idRole')
                ->on('role')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tache_role');
    }
};
