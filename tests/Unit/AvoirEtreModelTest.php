<?php

namespace Tests\Unit;

use App\Models\Avoir;
use App\Models\Etre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvoirEtreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_avoir_model_basic(): void
    {
        // Create a pivot record via DB to exercise the model behavior
        $user = \App\Models\Utilisateur::factory()->create();
        $role = \App\Models\Role::factory()->create();

        \Illuminate\Support\Facades\DB::table('avoir')->insert([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
            'model_type' => \App\Models\Utilisateur::class,
        ]);

        $avoir = Avoir::where('idUtilisateur', $user->idUtilisateur)->first();
        $this->assertNotNull($avoir);
    }

    public function test_etre_model_basic(): void
    {
        $classe = \App\Models\Classe::factory()->create();
        $famille = \App\Models\Famille::factory()->create();
        $idEnfant = rand(300000, 999999);
        $enfant = \App\Models\Enfant::factory()->create([
            'idEnfant' => $idEnfant,
            'idClasse' => $classe->idClasse,
            'idFamille' => $famille->idFamille,
        ]);

        \Illuminate\Support\Facades\DB::table('etre')->insert([
            'idEnfant' => $idEnfant,
            'activite' => 'cantine',
            'dateP' => now()->format('Y-m-d'),
        ]);

        $etre = Etre::where('idEnfant', $idEnfant)->first();
        $this->assertNotNull($etre);
    }
}
