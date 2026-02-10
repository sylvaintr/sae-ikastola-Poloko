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
        if (! Schema::hasTable('tache')) {
            Schema::create('tache', function (Blueprint $table) {
                $table->integer('idTache')->primary();
                $table->string('titre', 30);
                $table->string('description', 100);
                $table->string('type', 15);
                $table->string('urgence', 15)->default('Moyenne');
                $table->string('etat', 10);
                $table->date('dateD')->nullable();
                $table->date('dateF')->nullable();
                $table->decimal('montantP', 7)->nullable();
                $table->decimal('montantR', 7)->nullable();
                $table->integer('idEvenement')->nullable()->index('idevenement');
                $table->integer('idRole')->nullable()->index('idrole');
                $table->foreign('idRole')
                    ->references('idRole')
                    ->on('role')
                    ->nullOnDelete();
            });
        } else {
            // Si la table existe déjà, ajouter la colonne idRole si elle n'existe pas
            Schema::table('tache', function (Blueprint $table) {
                if (! Schema::hasColumn('tache', 'idRole')) {
                    $table->integer('idRole')->nullable()->after('idEvenement')->index('idrole');
                    $table->foreign('idRole')
                        ->references('idRole')
                        ->on('role')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache');
    }
};
