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
        Schema::create('tache', function (Blueprint $table) {
            $table->bigIncrements('idTache');
            $table->string('titre', 30);
            $table->string('description', 100);
            $table->string('type', 15);
            $table->string('etat', 10);
            $table->date('dateD')->nullable();
            $table->date('dateF')->nullable();
            $table->decimal('montantP', 7)->nullable();
            $table->decimal('montantR', 7)->nullable();
            $table->integer('idEvenement')->nullable()->index('idevenement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache');
    }
};
