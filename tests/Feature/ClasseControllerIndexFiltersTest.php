<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Classe;

class ClasseControllerIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filter_is_applied()
    {
        Classe::factory()->create(['nom' => 'Classe Alpha', 'niveau' => 'CP']);
        Classe::factory()->create(['nom' => 'Classe Beta', 'niveau' => 'CP']);

        $request = Request::create('/admin/classes', 'GET', ['search' => 'Alpha']);
        $controller = new \App\Http\Controllers\ClasseController();
        $view = $controller->index($request);

        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Classe Alpha', $classes->items()[0]->nom);
    }

    public function test_niveau_filter_is_applied()
    {
        Classe::factory()->create(['nom' => 'Classe Une', 'niveau' => 'CE1']);
        Classe::factory()->create(['nom' => 'Classe Deux', 'niveau' => 'CM2']);

        $request = Request::create('/admin/classes', 'GET', ['niveau' => 'CM2']);
        $controller = new \App\Http\Controllers\ClasseController();
        $view = $controller->index($request);

        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Classe Deux', $classes->items()[0]->nom);
    }

    public function test_combined_filters_are_applied()
    {
        Classe::factory()->create(['nom' => 'Alpha CE1', 'niveau' => 'CE1']);
        Classe::factory()->create(['nom' => 'Alpha CM2', 'niveau' => 'CM2']);
        Classe::factory()->create(['nom' => 'Beta CE1', 'niveau' => 'CE1']);

        $request = Request::create('/admin/classes', 'GET', ['search' => 'Alpha', 'niveau' => 'CE1']);
        $controller = new \App\Http\Controllers\ClasseController();
        $view = $controller->index($request);

        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Alpha CE1', $classes->items()[0]->nom);
    }
}
