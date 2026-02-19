<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For existing tâches (type is low/medium/high or null): copy type to urgence, set type='tache'
        DB::statement("UPDATE tache SET urgence = type WHERE type IN ('low', 'medium', 'high')");
        DB::statement("UPDATE tache SET type = 'tache' WHERE type IN ('low', 'medium', 'high') OR type IS NULL OR type = ''");
        // Demandes have type = NULL currently (field was removed from form)
        // They get type='demande' - but since urgence already stores Faible/Moyenne/Élevée for demandes, no urgence migration needed
        // Actually wait - all NULL types could be either old tâches or demandes - for now set remaining NULLs to 'tache' since demandes are newer
        DB::statement("UPDATE tache SET type = 'demande' WHERE type IS NULL AND urgence IN ('Faible', 'Moyenne', 'Élevée')");
        DB::statement("UPDATE tache SET type = 'tache' WHERE type IS NULL");
    }

    public function down(): void
    {
        // Reverse: restore type from urgence for tâches
        DB::statement("UPDATE tache SET type = urgence WHERE type = 'tache'");
        DB::statement("UPDATE tache SET type = NULL WHERE type = 'demande'");
    }
};
