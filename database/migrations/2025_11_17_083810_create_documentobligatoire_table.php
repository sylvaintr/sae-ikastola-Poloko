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
        Schema::create('documentobligatoire', function (Blueprint $table) {
            $table->bigIncrements('idDocumentObligatoire');
            $table->string('nom', 20)->nullable();
            $table->boolean('dateE')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentobligatoire');
    }
};
