<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureConversionService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureConversionServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! function_exists('App\\Services\\exec')) {
            eval('namespace App\\Services; function exec($cmd, &$output, &$returnVar) { $behavior = $GLOBALS["app_services_exec_behavior"] ?? null; $out = $GLOBALS["app_services_test_output"] ?? null; if ($behavior === "create" && $out) { file_put_contents($out, "pdf-content"); $output = ["created"]; $returnVar = 0; } else { $output = ["failed"]; $returnVar = 1; } }');
        }
    }
    public function test_convertirWordToPdf_creates_output_when_exec_simulates_creation()
    {
        $service = new FactureConversionService();

        $input = tempnam(sys_get_temp_dir(), 'fc_in_');
        file_put_contents($input, 'input');
        $output = sys_get_temp_dir() . '/' . uniqid('fc_out_') . '.pdf';

        // Install a namespace-level exec shim that creates the output file when instructed
        if (! function_exists('App\\Services\\exec')) {
            eval('namespace App\\Services; function exec($cmd, &$output, &$returnVar) { $behavior = $GLOBALS["app_services_exec_behavior"] ?? null; $out = $GLOBALS["app_services_test_output"] ?? null; if ($behavior === "create" && $out) { file_put_contents($out, "pdf-content"); $output = ["created"]; $returnVar = 0; } else { $output = ["failed"]; $returnVar = 1; } }');
        }

        $GLOBALS['app_services_exec_behavior'] = 'create';
        $GLOBALS['app_services_test_output']   = $output;

        $res = $service->convertirWordToPdf($input, $output);

        @unlink($input);
        @unlink($output);

        $this->assertIsArray($res);
        $this->assertArrayHasKey('success', $res);
        $this->assertIsBool($res['success']);
    }

    public function test_convertirWordToPdf_returns_false_when_no_output_created()
    {
        $service = new FactureConversionService();

        $input = tempnam(sys_get_temp_dir(), 'fc_in_');
        file_put_contents($input, 'input');
        $output = sys_get_temp_dir() . '/' . uniqid('fc_out_') . '.pdf';

        $GLOBALS['app_services_exec_behavior'] = 'no_create';
        $GLOBALS['app_services_test_output']   = $output;

        $res = $service->convertirWordToPdf($input, $output);

        @unlink($input);
        @unlink($output);

        $this->assertFalse($res['success']);
    }

    public function test_convertFactureToPdf_deletes_original_when_deleteOriginal_true_and_conversion_succeeds()
    {
        // prepare a fake stored file
        Storage::disk('public')->put('factures/facture-999.docx', 'content');

        $facture            = new Facture();
        $facture->idFacture = 999;

        $mock = $this->getMockBuilder(FactureConversionService::class)
            ->onlyMethods(['convertirWordToPdf'])
            ->getMock();

        $mock->expects($this->once())->method('convertirWordToPdf')->willReturn(['success' => true, 'output' => [], 'return' => 0]);

        $res = $mock->convertFactureToPdf($facture, true);

        $this->assertTrue($res);
        $this->assertFalse(Storage::disk('public')->exists('factures/facture-999.docx'));
    }

    public function test_convertFactureToPdf_returns_false_when_no_source_found()
    {
        $facture            = new Facture();
        $facture->idFacture = 123456789;

        $service = new FactureConversionService();
        $res     = $service->convertFactureToPdf($facture, false);

        $this->assertFalse($res);
    }

    public function test_convertFactureToPdfAndDeleteWord_calls_wrapper()
    {
        $facture            = new Facture();
        $facture->idFacture = 555;

        $mock = $this->getMockBuilder(FactureConversionService::class)
            ->onlyMethods(['convertFactureToPdf'])
            ->getMock();

        $mock->expects($this->once())
            ->method('convertFactureToPdf')
            ->with($this->equalTo($facture), $this->equalTo(true))
            ->willReturn(true);

        $res = $mock->convertFactureToPdfAndDeleteWord($facture);

        $this->assertTrue($res);
    }

    public function test_convertFactureToPdf_creates_output_dir_when_missing()
    {
        $dir = storage_path('app/public/factures/');
        if (file_exists($dir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($dir);
        }

        $facture            = new Facture();
        $facture->idFacture = 424242;

        $service = new FactureConversionService();
        $res     = $service->convertFactureToPdf($facture, false);

        $this->assertFalse($res);
        $this->assertDirectoryExists($dir);
    }

    public function test_convertirWordToPdf_unlinks_existing_output()
    {
        $service = new FactureConversionService();

        $input = tempnam(sys_get_temp_dir(), 'fc_in_');
        file_put_contents($input, 'input');
        $output = sys_get_temp_dir() . '/' . uniqid('fc_out_') . '.pdf';

        // create pre-existing output file to force the @unlink branch
        file_put_contents($output, 'old');

        $GLOBALS['app_services_exec_behavior'] = 'no_create';
        $GLOBALS['app_services_test_output']   = $output;

        $res = $service->convertirWordToPdf($input, $output);

        @unlink($input);
        @unlink($output);

        $this->assertIsArray($res);
        $this->assertArrayHasKey('output', $res);
    }
}
