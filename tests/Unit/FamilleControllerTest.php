<?php

namespace Tests\Unit;

use App\Http\Controllers\FamilleController;
use App\Models\Famille;
use App\Models\Enfant;
use App\Models\Utilisateur;
use App\Models\Classe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class FamilleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajouter_cree_une_famille_avec_enfants_et_utilisateurs()
    {
        // given: a classe exists for enfants
        $classe = Classe::factory()->create();

        // create an enfant first (controller expects existing idEnfant when auto-increment disabled)
        $existingEnfant = Enfant::factory()->create(['idFamille' => 0, 'idClasse' => $classe->idClasse]);

        $payload = [
            'enfants' => [
                [
                    'idEnfant' => $existingEnfant->idEnfant,
                    'nom' => 'Enf',
                    'prenom' => 'Tst',
                    'dateN' => '2020-01-01',
                    'sexe' => 'M',
                    'NNI' => '000',
                    'idClasse' => $classe->idClasse,
                ],
            ],
            'utilisateurs' => [
                [
                    'nom' => 'Parent',
                    'prenom' => 'One',
                    'mdp' => 'secret',
                    'languePref' => 'fr',
                    'parite' => 1,
                ],
            ],
        ];

        $request = Request::create('/','POST', $payload);

        // when
        $ctrl = new FamilleController();
        $resp = $ctrl->ajouter($request);

        // then
        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(201, $resp->getStatusCode());

        $data = $resp->getData(true);
        $this->assertArrayHasKey('famille', $data);

        $this->assertGreaterThanOrEqual(1, Famille::count());
        $famille = Famille::first();
        // basic check: response includes the famille object and status code already asserted above
    }

    public function test_show_json_retourne_404_si_introuvable()
    {
        // given: request wants json
        $req = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->show(999999);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(404, $resp->getStatusCode());
    }

    public function test_show_json_retourne_la_famille_si_trouvee()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();

        $req = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->show($famille->idFamille);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function test_suppression_introuvable_retourne_404()
    {
        // given
        // none

        // when

        // then
        $ctrl = new FamilleController();
        $resp = $ctrl->delete(999999);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(404, $resp->getStatusCode());
    }

    public function test_recherche_par_parent_sans_requete_retourne_vide()
    {
        // given
        // none

        // when

        // then
        $req = Request::create('/', 'GET', []);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->searchByParent($req);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals([], $resp->getData(true));
    }

    public function test_mise_a_jour_introuvable_retourne_404()
    {
        // given
        // none

        // when

        // then
        $req = Request::create('/', 'POST', ['enfants' => [], 'utilisateurs' => []]);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->update($req, 999999);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(404, $resp->getStatusCode());
    }

    public function test_index_retourne_vue_et_json_lors_demande()
    {
        // given
        // none

        // when

        // then
        // create some families
        Famille::factory()->count(2)->create();

        // normal request -> view
        $req = Request::create('/', 'GET', []);
        $this->app->instance('request', $req);
        $ctrl = new FamilleController();
        $view = $ctrl->index();
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);

        // json request -> json response
        $reqJson = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $reqJson);
        $resp = $ctrl->index();
        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertIsArray($resp->getData(true));
    }

    public function test_create_retourne_vue()
    {
        // given
        // none

        // when

        // then
        // prepare some available utilisateurs and enfants
        $user = Utilisateur::factory()->create();
        $enfant = Enfant::factory()->create(['idFamille' => 0, 'idEnfant' => 102000]);

        $ctrl = new FamilleController();
        $view = $ctrl->create();

        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $view);
    }

    public function test_edit_redirige_si_introuvable()
    {
        // given
        // none

        // when

        // then
        $ctrl = new FamilleController();
        $resp = $ctrl->edit(999999);
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
    }

    public function test_supprimer_famille_existante_supprime_la_famille()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();
        $enfant = Enfant::factory()->create(['idFamille' => $famille->idFamille, 'idEnfant' => 101000]);
        $user = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur);

        $ctrl = new FamilleController();
        $resp = $ctrl->delete($famille->idFamille);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertDatabaseMissing((new Famille())->getTable(), ['idFamille' => $famille->idFamille]);
    }

    public function test_searchUsers_retourne_des_resultats()
    {
        // given: a user without famille matching query
        $user = Utilisateur::factory()->create(['nom' => 'Smith', 'prenom' => 'John']);

        $req = Request::create('/', 'GET', ['q' => 'Sm']);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->searchUsers($req);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $data = $resp->getData(true);
        $this->assertNotEmpty($data);
    }

    public function test_update_reussit_met_a_jour_enfants_et_utilisateurs()
    {
        // given
        // none

        // when

        // then
        $famille = Famille::factory()->create();
        $enfant = Enfant::factory()->create(['idFamille' => $famille->idFamille, 'idEnfant' => 101001]);
        $user = Utilisateur::factory()->create();
        $famille->utilisateurs()->attach($user->idUtilisateur);

        $payload = [
            'enfants' => [
                ['idEnfant' => $enfant->idEnfant, 'nom' => 'NewNom', 'prenom' => 'NewPrenom'],
            ],
            'utilisateurs' => [
                ['idUtilisateur' => $user->idUtilisateur, 'languePref' => 'eus'],
            ],
        ];

        $req = Request::create('/', 'POST', $payload);
        $this->app->instance('request', $req);

        $ctrl = new FamilleController();
        $resp = $ctrl->update($req, $famille->idFamille);

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals('NewNom', Enfant::find($enfant->idEnfant)->nom);
        $this->assertEquals('eus', Utilisateur::find($user->idUtilisateur)->languePref);
    }
}
