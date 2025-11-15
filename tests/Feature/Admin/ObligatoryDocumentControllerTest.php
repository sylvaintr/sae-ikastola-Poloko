<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Models\DocumentObligatoire;
use App\Models\Utilisateur;

class ObligatoryDocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_documents()
    {
        $role1 = Role::factory()->create(['name' => 'CA']);
        $role2 = Role::factory()->create();

        $doc = DocumentObligatoire::factory()->create(['dateE' => true]);
        $doc->roles()->attach([$role1->idRole, $role2->idRole]);

        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $response = $this->actingAs($admin)->get(route('admin.obligatory_documents.index'));

        $response->assertStatus(200);
        $response->assertViewHas('documents');
    }

    public function test_create_returns_view()
    {
        Role::factory()->create(['name' => 'CA']);
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $response = $this->actingAs($admin)->get(route('admin.obligatory_documents.create'));
        $response->assertStatus(200);
        $response->assertViewHasAll(['roles', 'nomMaxLength']);
    }

    public function test_store_creates_document_and_syncs_roles()
    {
        $role1 = Role::factory()->create(['name' => 'CA']);
        $role2 = Role::factory()->create();

        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $payload = [
            'nom' => 'Permis',
            'expirationType' => 'none',
            'roles' => [$role1->idRole, $role2->idRole],
        ];

        $response = $this->actingAs($admin)->post(route('admin.obligatory_documents.store'), $payload);

        $response->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseHas('documentObligatoire', ['nom' => 'Permis']);
        $this->assertDatabaseHas('attribuer', ['idRole' => $role1->idRole]);
    }

    public function test_edit_update_and_destroy_flow()
    {
        $role1 = Role::factory()->create(['name' => 'CA']);
        $role2 = Role::factory()->create();
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');

        $doc = DocumentObligatoire::factory()->create();
        $doc->roles()->attach([$role1->idRole]);

        $response = $this->actingAs($admin)->get(route('admin.obligatory_documents.edit', $doc));
        $response->assertStatus(200);

        $updateData = [
            'nom' => 'NouveauNom',
            'expirationType' => 'none',
            'roles' => [$role2->idRole],
        ];

        $response = $this->actingAs($admin)->put(route('admin.obligatory_documents.update', $doc), $updateData);
        $response->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseHas('documentObligatoire', ['nom' => 'NouveauNom']);
        $this->assertDatabaseHas('attribuer', ['idRole' => $role2->idRole]);

        $response = $this->actingAs($admin)->delete(route('admin.obligatory_documents.destroy', $doc));
        $response->assertRedirect(route('admin.obligatory_documents.index'));

        $this->assertDatabaseMissing('documentObligatoire', ['idDocumentObligatoire' => $doc->idDocumentObligatoire]);
    }
}
