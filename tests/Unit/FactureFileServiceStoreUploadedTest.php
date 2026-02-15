<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureConversionService;
use App\Services\FactureFileService;
use Tests\TestCase;

class FactureFileServiceStoreUploadedTest extends TestCase
{
    public function test_storeUploadedFacture_sets_default_extension_when_invalid()
    {
        $mockConv = $this->createMock(FactureConversionService::class);
        $mockConv->expects($this->once())->method('convertirWordToPdf');

        $service = new FactureFileService($mockConv);

        $file = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('exe');
        $file->method('extension')->willReturn('exe');
        $file->method('storeAs')->willReturn('public/factures/facture-1.docx');

        $facture            = new Facture();
        $facture->idFacture = 1;

        $res = $service->storeUploadedFacture($file, $facture);

        $this->assertIsString($res);
        $this->assertStringEndsWith('.docx', $res);
    }

    public function test_storeUploadedFacture_returns_false_when_store_fails()
    {
        $mockConv = $this->createMock(FactureConversionService::class);
        $mockConv->expects($this->never())->method('convertirWordToPdf');

        $service = new FactureFileService($mockConv);

        $file = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('docx');
        $file->method('extension')->willReturn('docx');
        $file->method('storeAs')->willReturn(false);

        $facture            = new Facture();
        $facture->idFacture = '9999';

        $res = $service->storeUploadedFacture($file, $facture);

        $this->assertFalse($res);
    }
}
