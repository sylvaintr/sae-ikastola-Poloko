<?php

namespace Tests\Unit;

use App\Models\Avoir;
use App\Models\Pratiquer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Classe;
use App\Models\Famille;
use App\Models\Enfant;

class AvoirPratiquerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_modele_avoir_de_base(): void
    {
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

    public function test_modele_pratiquer_de_base(): void
    {
        $classe = Classe::factory()->create();
        $famille = Famille::factory()->create();
        $idEnfant = random_int(300000, 999999);

        Enfant::factory()->create([
            'idEnfant' => $idEnfant,
            'idClasse' => $classe->idClasse,
            'idFamille' => $famille->idFamille,
        ]);

        \Illuminate\Support\Facades\DB::table('pratiquer')->insert([
            'idEnfant' => $idEnfant,
            'activite' => 'cantine',
            'dateP' => now()->format('Y-m-d'),
        ]);

        $pratiquer = Pratiquer::where('idEnfant', $idEnfant)->first();
        $this->assertNotNull($pratiquer);
    }
}
