<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Utilisateur;

class AccountControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_accounts_and_search_filters()
    {
        $this->withoutMiddleware();

        // Create some users
        Utilisateur::factory()->create(['prenom' => 'Alice', 'nom' => 'Dupont', 'email' => 'alice@example.com']);
        Utilisateur::factory()->create(['prenom' => 'Bob', 'nom' => 'Martin', 'email' => 'bob@example.com']);
        Utilisateur::factory()->create(['prenom' => 'Charlie', 'nom' => 'Durand', 'email' => 'charlie@example.com']);

        $controller = new \App\Http\Controllers\Admin\AccountController();
        $request = new \Illuminate\Http\Request();
        $view = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $data = $view->getData();
        $this->assertArrayHasKey('accounts', $data);
        $accounts = $data['accounts'];
        $this->assertGreaterThanOrEqual(3, $accounts->total());

        // Test search filter
        $request2 = new \Illuminate\Http\Request(['search' => 'Alice']);
        $view2 = $controller->index($request2);
        $data2 = $view2->getData();
        // Ensure at least one matching account is returned and specifically 'Alice' exists
        $this->assertGreaterThanOrEqual(1, $data2['accounts']->total());
        $found = false;
        foreach ($data2['accounts'] as $acct) {
            if (($acct->prenom ?? '') === 'Alice') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Search results should contain the account with prenom "Alice"');
    }
}
