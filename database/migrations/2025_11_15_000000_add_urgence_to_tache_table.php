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
        Schema::table('tache', function (Blueprint $table) {
            if (!Schema::hasColumn('tache', 'urgence')) {
                $table->string('urgence', 15)->default('Moyenne')->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tache', function (Blueprint $table) {
            if (Schema::hasColumn('tache', 'urgence')) {
                $table->dropColumn('urgence');
            }
        });
    }
};

