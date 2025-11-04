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
        Schema::create('document_obligatoire', function (Blueprint $table) {
            $table->integer('idDocumentObligatoire')->primary();
            $table->string('nom', 20)->nullable();
            $table->boolean('dateE')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_obligatoire');
    }
};
