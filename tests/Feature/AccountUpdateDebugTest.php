<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Utilisateur;

class AccountUpdateDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_capture_queries_during_put_update()
    {
        // Create admin user and authenticate so role middleware passes and SubstituteBindings runs
        $adminRole = Role::factory()->create(['name' => 'CA']);
        $adminUser = Utilisateur::factory()->create();
        $adminUser->rolesCustom()->attach($adminRole->idRole, ['model_type' => Utilisateur::class]);
        $this->actingAs($adminUser);

        $role = Role::factory()->create();
        $account = Utilisateur::factory()->create();

        $putData = [
            'prenom' => 'DbgPrenom',
            'nom' => 'DbgNom',
            'email' => 'dbg@example.com',
            'languePref' => 'fr',
            'statutValidation' => false,
            'roles' => [$role->idRole],
        ];

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = ['sql' => $query->sql, 'bindings' => $query->bindings];
        });

        $response = $this->put(route('admin.accounts.update', $account->idUtilisateur), $putData);

        // Find any insert into `avoir` in captured queries
        $pivotInserts = array_filter($queries, function ($q) {
            return str_contains($q['sql'], 'insert into `avoir`');
        });

        // Assert we captured at least one pivot insert attempt
        $this->assertNotEmpty($pivotInserts, 'No pivot insert queries captured; queries: ' . json_encode($queries));

        // Inspect the first pivot insert bindings for diagnostic purposes
        $first = array_values($pivotInserts)[0];
        // Expect the bindings to include both the account id and the role id (numeric bindings)
        $bindings = $first['bindings'];
        $this->assertTrue(in_array($account->idUtilisateur, $bindings, true), 'Expected account id in pivot bindings; bindings: ' . json_encode($bindings));
        $this->assertTrue(in_array($role->idRole, $bindings, true), 'Expected role id in pivot bindings; bindings: ' . json_encode($bindings));
    }
}
