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
        // given
        Classe::factory()->create(['nom' => 'Classe Alpha', 'niveau' => 'CP']);
        Classe::factory()->create(['nom' => 'Classe Beta', 'niveau' => 'CP']);
        $controller = new \App\Http\Controllers\ClasseController();

        // when
        $request = Request::create('/admin/classes', 'GET', ['search' => 'Alpha']);
        $view = $controller->index($request);

        // then
        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Classe Alpha', $classes->items()[0]->nom);
    }

    public function test_niveau_filter_is_applied()
    {
        // given
        // Supprimer les classes existantes avec ces niveaux pour éviter les interférences
        Classe::where('niveau', 'CE1')->orWhere('niveau', 'CM2')->delete();

        Classe::factory()->create(['nom' => 'Classe Une', 'niveau' => 'CE1']);
        Classe::factory()->create(['nom' => 'Classe Deux', 'niveau' => 'CM2']);
        $controller = new \App\Http\Controllers\ClasseController();

        // when
        $request = Request::create('/admin/classes', 'GET', ['niveau' => 'CM2']);
        $view = $controller->index($request);

        // then
        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Classe Deux', $classes->items()[0]->nom);
    }

    public function test_combined_filters_are_applied()
    {
        // given
        Classe::factory()->create(['nom' => 'Alpha CE1', 'niveau' => 'CE1']);
        Classe::factory()->create(['nom' => 'Alpha CM2', 'niveau' => 'CM2']);
        Classe::factory()->create(['nom' => 'Beta CE1', 'niveau' => 'CE1']);
        $controller = new \App\Http\Controllers\ClasseController();

        // when
        $request = Request::create('/admin/classes', 'GET', ['search' => 'Alpha', 'niveau' => 'CE1']);
        $view = $controller->index($request);

        // then
        $data = $view->getData();
        $classes = $data['classes'];

        $this->assertEquals(1, $classes->total());
        $this->assertEquals('Alpha CE1', $classes->items()[0]->nom);
    }
}
