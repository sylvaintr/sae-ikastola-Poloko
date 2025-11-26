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
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->integer('idUtilisateur', true);
            $table->string('prenom', 15);
            $table->string('nom', 15);
            $table->string('mdp', 255);
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('languePref', 17);
            $table->boolean('statutValidation')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
