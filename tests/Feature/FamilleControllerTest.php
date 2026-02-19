<?php
namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Famille;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FamilleControllerTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure CA role exists
        if (Role::where('name', 'CA')->count() == 0) {
            Role::create(['name' => 'CA']);
        }

        // Ensure parent role exists for tests that expect it
        if (Role::where('name', 'parent')->count() == 0) {
            Role::create(['name' => 'parent']);
        }

        $this->adminUser = Utilisateur::factory()->create();
        $this->adminUser->roles()->attach(Role::where('name', 'CA')->first());

        // Ensure the required permission exists and assign it to the CA role (if permission tables exist)
        if (Schema::hasTable('permissions')) {
            if (Permission::where('name', 'gerer-familles')->count() === 0) {
                Permission::create(['name' => 'gerer-familles']);
            }
            $role = Role::where('name', 'CA')->first();
            if ($role && ! $role->hasPermissionTo('gerer-familles')) {
                $role->givePermissionTo('gerer-familles');
            }
        }
    }

    public function test_api_index_retourne_liste_json()
    {
        // given
        Famille::factory()->count(3)->create();

        // when
        $response = $this->getJson('/api/familles');

        // then
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    public function test_web_admin_index_retourne_vue()
    {
        // given
        // admin user prepared in setUp

        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.index'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.index');
        $response->assertViewHas('familles');
    }

    public function test_api_show_retourne_famille_json()
    {
        // given
        $famille = Famille::factory()->create();

        // when
        $response = $this->getJson('/api/familles/' . $famille->idFamille);

        // then
        $response->assertStatus(200)
            ->assertJsonFragment(['idFamille' => $famille->idFamille]);
    }

    public function test_web_admin_show_retourne_vue()
    {
        // given
        $famille = Famille::factory()->create();

        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.show', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.show');
        $response->assertViewHas('famille');
    }

    public function test_show_retourne_404_si_json_manquant()
    {
        // when
        $response = $this->getJson('/api/familles/999999999');

        // then
        $response->assertStatus(404);
    }

    public function test_web_admin_show_redirects_if_missing()
    {
        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.show', 99999999));

        // then
        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_web_create_retourne_vue()
    {
        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.create'));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create');
        $response->assertViewHas(['tousUtilisateurs', 'tousEnfants']);
    }

    public function test_web_edit_retourne_vue()
    {
        // given
        $famille = Famille::factory()->create();

        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.edit', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $response->assertViewIs('admin.familles.create'); // It reuses create view
        $response->assertViewHas('famille');
    }

    public function test_web_edit_redirige_si_manquant()
    {
        // when
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.edit', 999999));

        // then
        $response->assertRedirect(route('admin.familles.index'));
    }

    public function test_ajouter_creates_family_json()
    {
        // given
        $classe = Classe::factory()->create();
        $user   = Utilisateur::factory()->create();

        $payload = [
            'enfants'      => [
                [
                    'nom'      => 'Dupont',
                    'prenom'   => 'Alice',
                    'dateN'    => '2015-05-01',
                    'sexe'     => 'F',
                    'NNI'      => '123456789',
                    'idClasse' => $classe->idClasse,
                ],
            ],
            'utilisateurs' => [
                ['idUtilisateur' => $user->idUtilisateur, 'parite' => 100],
            ],
        ];

        // when
        $response = $this->postJson('/api/familles', $payload);

        // then
        $response->assertStatus(201);

        $idFamille = $response->json('famille.idFamille');
        $this->assertDatabaseHas('famille', ['idFamille' => $idFamille]);
        $this->assertDatabaseHas('enfant', ['nom' => 'Dupont', 'idFamille' => $idFamille]);
        $this->assertDatabaseHas('lier', ['idFamille' => $idFamille, 'idUtilisateur' => $user->idUtilisateur]);
    }

    public function test_delete_removes_family_as_admin()
    {
        // given
        $famille = Famille::factory()->create();

        // when
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.familles.delete', $famille->idFamille));

        // then
        $response->assertStatus(200);
        $this->assertDatabaseMissing('famille', ['idFamille' => $famille->idFamille]);
    }

    public function test_suppression_retourne_404_si_introuvable()
    {
        // when
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.familles.delete', 999999));

        // then
        $response->assertStatus(404);
    }

    public function test_search_by_parent()
    {
        // given
        $famille = Famille::factory()->create();
        $user    = Utilisateur::factory()->create(['nom' => 'SearchableName']);
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 100]);

        // when
        $response = $this->getJson('/api/search?q=Searchable');

        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'SearchableName']);
    }

    public function test_recherche_par_parent_requete_courte_retourne_erreur_validation()
    {
        // when
        $response = $this->getJson('/api/search?q=a');

        // then
        $response->assertStatus(422);
    }

    public function test_search_users()
    {
        // given
        $user = Utilisateur::factory()->create(['nom' => 'UserFind']);

        // Ensure role 'parent' exists and assign to the user so controller's
        // role-based filter includes this user.
        if (Role::where('name', 'parent')->count() === 0) {
            Role::create(['name' => 'parent']);
        }
        $user->assignRole('parent');

        // This route is defined in web.php and requires Auth
        // when
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/search/users?q=UserFind');

        // then
        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'UserFind']);
    }

    public function test_update_detaches_enfants_removed_from_payload()
    {
        // 1. On crée une famille avec deux enfants
        $famille         = Famille::factory()->create();
        $enfantAGarder   = Enfant::factory()->create(['idFamille' => $famille->idFamille]);
        $enfantADetacher = Enfant::factory()->create(['idFamille' => $famille->idFamille]);

        // 2. On met à jour la famille en ne renvoyant QU'UN SEUL enfant
        $data = [
            'enfants'      => [
                [
                    'idEnfant' => $enfantAGarder->idEnfant,
                    'nom'      => $enfantAGarder->nom,
                    'prenom'   => $enfantAGarder->prenom,
                ],
            ],
            'utilisateurs' => [],
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('admin.familles.update', $famille->idFamille), $data);

        $response->assertStatus(200);

                                             // 3. Vérifications : la ligne 51 a dû passer idFamille à null pour l'enfant manquant
        $this->assertDatabaseHas('enfant', [ // ou 'enfants' selon le nom exact de ta table
            'idEnfant'  => $enfantAGarder->idEnfant,
            'idFamille' => $famille->idFamille,
        ]);

        $this->assertDatabaseHas('enfant', [
            'idEnfant'  => $enfantADetacher->idEnfant,
            'idFamille' => null, // La preuve que la ligne a été exécutée
        ]);
    }

    public function test_update_silently_ignores_non_existent_enfant()
    {
        $famille = Famille::factory()->create();

        // On envoie un ID d'enfant complètement fantôme (ex: 99999)
        $idFantome = 99999;

        $data = [
            'enfants'      => [
                [
                    'idEnfant' => $idFantome,
                    'nom'      => 'Inconnu',
                    'prenom'   => 'Fantome',
                ],
            ],
            'utilisateurs' => [],
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('admin.familles.update', $famille->idFamille), $data);

        // Si le "return;" fonctionne, l'application ne crashe pas et renvoie 200
        $response->assertStatus(200);

        // On s'assure qu'aucun enfant n'a été accidentellement créé avec cet ID
        $this->assertDatabaseMissing('enfant', [
            'idEnfant' => $idFantome,
        ]);
    }
    public function test_update_utilisateur_hashes_and_saves_new_password_if_provided()
    {
        $famille = Famille::factory()->create();

        // Utilisateur avec un ancien mot de passe connu
        $user = Utilisateur::factory()->create([
            'mdp' => bcrypt('ancien_mot_de_passe'),
        ]);
        $famille->utilisateurs()->attach($user->idUtilisateur);

        $nouveauMdp = 'NouveauMotDePasseSuperSecurise123!';

        // On envoie une requête de mise à jour avec le champ 'mdp'
        $data = [
            'enfants'      => [],
            'utilisateurs' => [
                [
                    'idUtilisateur' => $user->idUtilisateur,
                    'nom'           => $user->nom,
                    'mdp'           => $nouveauMdp, // C'est ici que ça déclenche la ligne 208
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('admin.familles.update', $famille->idFamille), $data);

        $response->assertStatus(200);

        // On recharge l'utilisateur depuis la base
        $user->refresh();

        // On vérifie que le mot de passe correspond bien à la NOUVELLE valeur cryptée
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($nouveauMdp, $user->mdp));
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('ancien_mot_de_passe', $user->mdp));
    }

    public function test_create_filters_utilisateurs_by_parent_role()
    {
        $roleParent = Role::where('name', 'parent')->first();
        $roleAdmin  = Role::create(['name' => 'admin']);

        // Utilisateur avec rôle parent
        $parent = Utilisateur::factory()->create();
        $parent->rolesCustom()->attach($roleParent->idRole, ['model_type' => Utilisateur::class]);

        // Utilisateur SANS rôle parent
        $admin = Utilisateur::factory()->create();
        $admin->rolesCustom()->attach($roleAdmin->idRole, ['model_type' => Utilisateur::class]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.create'));

        $response->assertStatus(200);
        $tousUtilisateurs = $response->viewData('tousUtilisateurs');

        // L'utilisateur parent doit être là, mais pas l'admin (prouve que la ligne 83 fonctionne)
        $this->assertTrue($tousUtilisateurs->contains('idUtilisateur', $parent->idUtilisateur));
        $this->assertFalse($tousUtilisateurs->contains('idUtilisateur', $admin->idUtilisateur));
    }

    /**
     * Cible Lignes 118 & 245 : Filtrage complexe des utilisateurs dans edit() et searchUsers()
     * Ligne 118 : $q2->where('role.idRole', $roleParent->idRole);
     * Ligne 245 : $q->orWhereIn('idUtilisateur', $idsUtilisateursFamille);
     */
    public function test_edit_and_search_users_filter_by_parent_role_and_existing_family_members()
    {
        $roleParent = Role::where('name', 'parent')->first();

        $famille = Famille::factory()->create();

        // Cas 1 : Utilisateur Parent normal
        $parent = Utilisateur::factory()->create();
        $parent->rolesCustom()->attach($roleParent->idRole, ['model_type' => Utilisateur::class]);

        // Cas 2 : Utilisateur lambda (NON parent) mais DÉJÀ dans la famille (déclenche la ligne 245)
        $membreFamille = Utilisateur::factory()->create();
        // On l'attache à la famille SANS lui donner le rôle parent
        $famille->utilisateurs()->attach($membreFamille->idUtilisateur);

        // --- Test de la route EDIT (Ligne 118) ---
        $responseEdit         = $this->actingAs($this->adminUser)
            ->get(route('admin.familles.edit', $famille->idFamille));
        $tousUtilisateursEdit = $responseEdit->viewData('tousUtilisateurs');

        $this->assertTrue($tousUtilisateursEdit->contains('idUtilisateur', $parent->idUtilisateur));
        $this->assertTrue($tousUtilisateursEdit->contains('idUtilisateur', $membreFamille->idUtilisateur));

        // --- Test de la route SEARCH USERS (Lignes 118 & 245) ---
        $responseSearch = $this->actingAs($this->adminUser)
            ->getJson(route('admin.familles.searchUsers', ['famille_id' => $famille->idFamille]));

        $jsonSearch = collect($responseSearch->json());

        // La ligne 118 ajoute le parent
        $this->assertTrue($jsonSearch->contains('idUtilisateur', $parent->idUtilisateur));
        // La ligne 245 ajoute le membre lambda de la famille
        $this->assertTrue($jsonSearch->contains('idUtilisateur', $membreFamille->idUtilisateur));
    }

    /**
     * Cible Ligne 168-171 : Impossible de supprimer une famille si des factures sont associées
     */
    public function test_delete_fails_with_422_if_famille_has_factures()
    {
        $famille = Famille::factory()->create();

        // Création d'une facture liée à cette famille pour déclencher la protection
        \App\Models\Facture::factory()->create(['idFamille' => $famille->idFamille]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('admin.familles.delete', $famille->idFamille));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Impossible de supprimer la famille : des factures sont associées',
            'error'   => 'HAS_FACTURES',
        ]);

        // La famille ne doit pas avoir été supprimée
        $this->assertDatabaseHas('famille', ['idFamille' => $famille->idFamille]);
    }

    /**
     * Cible Lignes 229-231 : Récupération des IDs utilisateurs d'une famille dans searchUsers()
     */
    public function test_search_users_fetches_ids_utilisateurs_famille_if_id_provided()
    {
        // Pour atteindre ces lignes, il suffit de passer un 'famille_id' valide à la route searchUsers
        $famille = Famille::factory()->create();
        $user    = Utilisateur::factory()->create(['prenom' => 'TestUnqiue']);
        $famille->utilisateurs()->attach($user->idUtilisateur);

        // Cette requête va forcer le code à entrer dans le if ($famille) de la ligne 230
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.familles.searchUsers', [
                'famille_id' => $famille->idFamille,
                'q'          => 'TestUnqiue',
            ]));

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json())->contains('idUtilisateur', $user->idUtilisateur));
    }

    /**
     * Cible Lignes 285-286 : Mise à jour de aineDansAutreSeaska
     */
    public function test_update_modifies_aine_dans_autre_seaska_field()
    {
        // On crée une famille avec le statut false
        $famille = Famille::factory()->create(['aineDansAutreSeaska' => false]);

        $data = [
            'aineDansAutreSeaska' => true, // On veut déclencher le IF et la ligne 285
            'enfants'             => [],
            'utilisateurs'        => [],
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('admin.familles.update', $famille->idFamille), $data);

        $response->assertStatus(200);

        // On vérifie que le booléen a bien été mis à jour et sauvegardé (Ligne 286)
        $this->assertDatabaseHas('famille', [
            'idFamille'           => $famille->idFamille,
            'aineDansAutreSeaska' => 1, // true
        ]);
    }

    /**
     * Cible Ligne 338 : Génération d'un mot de passe aléatoire (Str::random) à la création d'un utilisateur
     */
    public function test_create_utilisateurs_generates_random_password_if_not_provided()
    {
        $data = [
            'aineDansAutreSeaska' => false,
            'enfants'             => [],
            'utilisateurs'        => [
                [
                    // Sans 'idUtilisateur', le code passe dans le 'else' (création)
                    'nom'    => 'Nouveau',
                    'prenom' => 'User',
                    'email'  => 'random@test.com',
                    // On omet volontairement 'mdp' pour déclencher la ligne 338
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.familles.store'), $data);

        $response->assertStatus(201);

        $userCree = Utilisateur::where('email', 'random@test.com')->first();

        $this->assertNotNull($userCree);
        // On vérifie que la ligne a bien crypté et enregistré un mot de passe (il n'est pas vide)
        $this->assertNotNull($userCree->mdp);
    }
}
