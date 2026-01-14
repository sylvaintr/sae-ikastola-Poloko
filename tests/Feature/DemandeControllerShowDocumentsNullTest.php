<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tache;

class DemandeControllerShowDocumentsNullTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_uses_empty_photos_when_documents_is_null()
    {
        // given
        $this->withoutMiddleware();
        $demande = Tache::factory()->create();

        // Force the documents property to null to hit the "documents ? ... : []" false branch
        $demande->documents = null;

        // when
        $controller = new \App\Http\Controllers\DemandeController();
        $resp = $controller->show($demande);

        // then
        $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $resp);
        $data = $resp->getData();
        $this->assertArrayHasKey('photos', $data);
        $this->assertSame([], $data['photos']);
    }
}
