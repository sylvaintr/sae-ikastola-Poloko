<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tache', function (Blueprint $table) {
            $table->string('type', 15)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tache', function (Blueprint $table) {
            $table->string('type', 15)->nullable(false)->default(null)->change();
        });
    }
};
