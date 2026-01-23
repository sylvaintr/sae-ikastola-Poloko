<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recette', function (Blueprint $table) {
            $table->increments('idRecette');

            $table->integer('idEvenement')->index('idevenement');

            $table->string('description', 100);
            $table->string('prix', 50);
            $table->string('quantite', 50);
            $table->string('type', 50)->default('recette');

            $table->foreign('idEvenement')
                ->references('idEvenement')
                ->on('evenement')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recette');
    }
};
