<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evenement_role', function (Blueprint $table) {
            $table->integer('idEvenement');
            $table->integer('idRole');

            $table->primary(['idEvenement', 'idRole']);

            $table->index('idEvenement');
            $table->index('idRole');

            // Si tes tables sont en InnoDB et que tu veux être strict :
            $table->foreign('idEvenement')
                ->references('idEvenement')
                ->on('evenement')
                ->onDelete('cascade');

            $table->foreign('idRole')
                ->references('idRole')
                ->on('role')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenement_role');
    }
};
