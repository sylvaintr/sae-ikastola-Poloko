<?php

namespace Tests\Unit;

use App\Models\Avoir;
use App\Models\Etre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Classe;
use App\Models\Famille;
use App\Models\Enfant;

class AvoirEtreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_avoir_model_basic(): void
    {
        // Create a pivot record via DB to exercise the model behavior
        $user = Utilisateur::factory()->create();
        $role = Role::factory()->create();

        \Illuminate\Support\Facades\DB::table('avoir')->insert([
            'idUtilisateur' => $user->idUtilisateur,
            'idRole' => $role->idRole,
            'model_type' => Utilisateur::class,
        ]);

        $avoir = Avoir::where('idUtilisateur', $user->idUtilisateur)->first();
        $this->assertNotNull($avoir);
    }

    public function test_etre_model_basic(): void
    {
        $classe = Classe::factory()->create();
        $famille = Famille::factory()->create();
        $idEnfant = rand(300000, 999999);
        Enfant::factory()->create([
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
