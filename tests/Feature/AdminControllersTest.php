<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\Utilisateur;
use App\Models\DocumentObligatoire;

class AdminControllersTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_store_creates_account_and_redirects()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();

        $postData = [
            'prenom' => 'Jean',
            'nom' => 'Dupont',
            'email' => 'j.dupont@example.com',
            'languePref' => 'fr',
            'mdp' => 'password123',
            'mdp_confirmation' => 'password123',
            'statutValidation' => true,
            'roles' => [$role->idRole],
        ];

        $response = $this->post(route('admin.accounts.store'), $postData);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => 'j.dupont@example.com']);
    }

    public function test_account_update_modifies_account()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();
        // Create with a high random primary key to avoid collisions during the full suite
        $account = Utilisateur::factory()->create(['idUtilisateur' => random_int(1000, 999999)]);
        $account = Utilisateur::find($account->idUtilisateur);

        $putData = [
            'prenom' => 'NewPrenom',
            'nom' => 'NewNom',
            // Use a deterministic email expected by the assertion
            'email' => 'newemail@example.com',
            'languePref' => 'en',
            'statutValidation' => false,
            'roles' => [$role->idRole],
        ];

        $response = $this->put(route('admin.accounts.update', $account->idUtilisateur), $putData);

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['email' => 'newemail@example.com', 'prenom' => 'NewPrenom']);
    }

    public function test_account_validate_sets_statutValidation()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        // Use an uncommon explicit ID to avoid collisions in the full suite
        $account = Utilisateur::factory()->create(['idUtilisateur' => random_int(1000, 999999), 'statutValidation' => false]);
        $account = Utilisateur::find($account->idUtilisateur);

        $response = $this->patch(route('admin.accounts.validate', $account->idUtilisateur));

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('utilisateur', ['idUtilisateur' => $account->idUtilisateur, 'statutValidation' => 1]);
    }

    public function test_account_destroy_deletes()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $account = Utilisateur::factory()->create(['idUtilisateur' => random_int(1000, 999999)]);
        $account = Utilisateur::find($account->idUtilisateur);

        $response = $this->delete(route('admin.accounts.destroy', $account->idUtilisateur));

        $response->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseMissing('utilisateur', ['idUtilisateur' => $account->idUtilisateur]);
    }

    // ObligatoryDocumentController tests
    public function test_obligatory_document_store_and_destroy()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();

        $postData = [
            'nom' => 'Piece justificative',
            'expirationType' => 'delai',
            'delai' => 30,
            'roles' => [$role->idRole],
        ];

        $response = $this->post(route('admin.obligatory_documents.store'), $postData);

        $response->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseHas('documentObligatoire', ['nom' => 'Piece justificative']);

        // Re-fetch the created document (factory sets idDocumentObligatoire in factory)
        $doc = DocumentObligatoire::first();
        $doc = DocumentObligatoire::find($doc->idDocumentObligatoire);

        $deleteResp = $this->delete(route('admin.obligatory_documents.destroy', $doc->idDocumentObligatoire));

        $deleteResp->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseMissing('documentObligatoire', ['idDocumentObligatoire' => $doc->idDocumentObligatoire]);
    }

    public function test_obligatory_document_update_modifies()
    {
        // Authenticate as CA role so middleware passes
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();
        $doc = DocumentObligatoire::factory()->create(['idDocumentObligatoire' => 81428, 'nom' => 'OldName']);
        $doc = DocumentObligatoire::find($doc->idDocumentObligatoire);

        $putData = [
            'nom' => 'UpdatedName',
            'expirationType' => 'none',
            'roles' => [$role->idRole],
        ];

        $response = $this->put(route('admin.obligatory_documents.update', $doc->idDocumentObligatoire), $putData);

        $response->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseHas('documentObligatoire', ['idDocumentObligatoire' => $doc->idDocumentObligatoire, 'nom' => 'UpdatedName']);
    }
}
