<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureConversionService;
use App\Services\FactureFileService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureFileServiceTest extends TestCase
{
    public function test_isValidFileMagic_detects_zip_and_ole_headers()
    {
        $tmpZip = tempnam(sys_get_temp_dir(), 'ff_');
        file_put_contents($tmpZip, "\x50\x4B\x03\x04rest");

        $tmpOle = tempnam(sys_get_temp_dir(), 'ff2_');
        file_put_contents($tmpOle, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1rest");

        $conversionMock = $this->createMock(FactureConversionService::class);
        $service        = new FactureFileService($conversionMock);

        $fakeFileZip = new class($tmpZip)
        {private $p;public function __construct($p)
            {$this->p = $p;}public function getRealPath()
            {return $this->p;}public function getClientOriginalExtension()
            {return 'zip';}};
        $fakeFileOle = new class($tmpOle)
        {private $p;public function __construct($p)
            {$this->p = $p;}public function getRealPath()
            {return $this->p;}public function getClientOriginalExtension()
            {return 'doc';}};

        $this->assertTrue($service->isValidFileMagic($fakeFileZip));
        $this->assertTrue($service->isValidFileMagic($fakeFileOle));

        @unlink($tmpZip);
        @unlink($tmpOle);
    }

    public function test_deleteExistingFactureFiles_deletes_all_formats()
    {
        Storage::disk('public')->put('factures/facture-77.doc', 'a');
        Storage::disk('public')->put('factures/facture-77.docx', 'b');
        Storage::disk('public')->put('factures/facture-77.odt', 'c');

        $conversionMock = $this->createMock(FactureConversionService::class);
        $service        = new FactureFileService($conversionMock);

        $this->assertTrue(Storage::disk('public')->exists('factures/facture-77.doc'));

        $service->deleteExistingFactureFiles('77');

        $this->assertFalse(Storage::disk('public')->exists('factures/facture-77.doc'));
        $this->assertFalse(Storage::disk('public')->exists('factures/facture-77.docx'));
        $this->assertFalse(Storage::disk('public')->exists('factures/facture-77.odt'));
    }

    public function test_storeUploadedFacture_stores_file_and_calls_conversion()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'up_');
        file_put_contents($tmp, 'payload');

        $facture            = new Facture();
        $facture->idFacture = 88;

        $conversionMock = $this->getMockBuilder(FactureConversionService::class)
            ->onlyMethods(['convertirWordToPdf'])
            ->getMock();

        $conversionMock->expects($this->once())->method('convertirWordToPdf')->willReturn(['success' => true, 'output' => [], 'return' => 0]);

        $service = new FactureFileService($conversionMock);

        $fakeUploaded = new class($tmp)
        {
            private $p;public function __construct($p)
            {$this->p = $p;}
            public function getClientOriginalExtension()
            {return 'docx';}
            public function extension()
            {return 'docx';}
            public function getRealPath()
            {return $this->p;}
            public function storeAs($dir, $filename)
            {$target = storage_path('app/' . $dir . '/' . $filename);@mkdir(dirname($target), 0755, true);
                copy($this->p, $target);return $dir . '/' . $filename;}
            public function getClientOriginalName()
            {return 'orig.docx';}
        };

        $res = $service->storeUploadedFacture($fakeUploaded, $facture);

        $this->assertIsString($res);
        $this->assertTrue(Storage::disk('public')->exists('factures/' . $res));

        // cleanup
        Storage::disk('public')->delete('factures/' . $res);
        @unlink($tmp);
    }
}
