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
        Schema::create('etre', function (Blueprint $table) {
            $table->integer('idEnfant');
            $table->string('activite', 20);
            $table->date('dateP');

            $table->index(['activite', 'dateP'], 'activite');
            $table->primary(['idEnfant', 'activite', 'dateP']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etre');
    }
};
