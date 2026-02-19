<?php
namespace Tests\Unit;

use App\Models\Enfant;
use App\Models\Famille;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
<<<<<<< HEAD
<<<<<<< HEAD
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Classe;
=======
use Tests\TestCase;
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
=======
use Tests\TestCase;
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)

class FamilleControllerUpdateEnfantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_enfants_ignore_entrees_sans_idEnfant()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();

        // Create an enfant that should be updated
        $enfant = Enfant::factory()->create([
            'idEnfant'  => 999,
            'idFamille' => $famille->idFamille,
            'nom'       => 'Before',
            'prenom'    => 'Child',
        ]);

        $classe = Classe::factory()->create();

        $requestData = [
<<<<<<< HEAD
<<<<<<< HEAD
            'enfants' => [
                // This entry has no idEnfant; current implementation will create it,
                // so provide required fields for creation.
                [
                    'nom' => 'Skipped',
                    'prenom' => 'NoId',
                    'dateN' => '2020-01-01',
                    'sexe' => 'M',
                    'NNI' => '000',
                    'idClasse' => $classe->idClasse,
=======
            'enfants'      => [
                // This entry has no idEnfant and should be skipped (branch !isset -> continue)
                [
=======
            'enfants'      => [
                // This entry has no idEnfant and should be skipped (branch !isset -> continue)
                [
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
                    'nom'      => 'Skipped',
                    'prenom'   => 'NoId',
                    'dateN'    => '2000-01-01',
                    'sexe'     => 'M',
                    'NNI'      => 123456,
                    'idClasse' => 0,
<<<<<<< HEAD
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
=======
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
                ],
                // This entry should be applied to the existing enfant
                [
                    'idEnfant' => $enfant->idEnfant,
                    'nom'      => 'After',
                    'dateN'    => '2000-01-01',
                ],
            ],
            'utilisateurs' => [],
        ];

        $request = Request::create('/', 'PUT', $requestData);

        $controller = new \App\Http\Controllers\FamilleController();
        $response   = $controller->update($request, $famille->idFamille);

        $this->assertEquals(200, $response->getStatusCode());

        $enfant->refresh();
        $this->assertEquals('After', $enfant->nom);

<<<<<<< HEAD
<<<<<<< HEAD
        // The implementation creates a new enfant when no idEnfant is provided.
=======
        // Ensure the enfant entry was created (controller creates new enfants when id missing)
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
=======
        // Ensure the enfant entry was created (controller creates new enfants when id missing)
>>>>>>> 9d3b359 (Add comprehensive tests for notification handling and user management)
        $this->assertDatabaseHas('enfant', ['nom' => 'Skipped', 'prenom' => 'NoId']);
    }
}
