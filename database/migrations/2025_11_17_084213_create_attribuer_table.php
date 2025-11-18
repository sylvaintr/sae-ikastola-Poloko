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
        Schema::create('attribuer', function (Blueprint $table) {
            $table->integer('idRole');
            $table->integer('idDocumentObligatoire')->index('iddocumentobligatoire');

            $table->primary(['idRole', 'idDocumentObligatoire']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribuer');
    }
};
