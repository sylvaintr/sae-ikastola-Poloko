<?php
namespace Tests\Unit;

use App\Services\FactureCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactureCalculatorEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculerMontantFacture_returns_redirect_when_facture_missing()
    {

        // given
        $calculator = new FactureCalculator();

        // when
        $res = $calculator->calculerMontantFacture('nonexistent-id');

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
    }

    public function test_calculerRegularisation_returns_zero_when_facture_missing()
    {
        // given
        $calculator = new FactureCalculator();
        // when
        $res = $calculator->calculerRegularisation(999999999);
        // then
        $this->assertEquals(0, $res);
    }
}
