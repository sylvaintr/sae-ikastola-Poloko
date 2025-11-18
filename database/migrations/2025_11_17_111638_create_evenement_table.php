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
        Schema::create('evenement', function (Blueprint $table) {
            $table->bigIncrements('idEvenement');
            $table->string('titre', 20);
            $table->string('description', 100);
            $table->boolean('obligatoire');
            $table->date('dateE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenement');
    }
};
