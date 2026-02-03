<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Utilisateur;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('ics_token', 64)->nullable()->unique()->after('remember_token');
        });

        // Générer un token pour les utilisateurs existants
        Utilisateur::whereNull('ics_token')->each(function ($user) {
            $user->update(['ics_token' => Str::random(64)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropColumn('ics_token');
        });
    }
};
