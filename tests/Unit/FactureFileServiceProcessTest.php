<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Services\FactureConversionService;
use App\Services\FactureFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FactureFileServiceProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_processUploadedFile_returns_null_when_no_file()
    {
        $conv    = $this->createMock(FactureConversionService::class);
        $service = new FactureFileService($conv);

        $req     = new Request();
        $facture = Facture::factory()->create();

        $res = $service->processUploadedFile($req, $facture);
        $this->assertNull($res);
    }

    public function test_processUploadedFile_returns_redirect_when_invalid_magic()
    {
        $conv    = $this->createMock(FactureConversionService::class);
        $service = $this->getMockBuilder(FactureFileService::class)->setConstructorArgs([$conv])->onlyMethods(['isValidFileMagic'])->getMock();
        $service->method('isValidFileMagic')->willReturn(false);

        $tmp = tempnam(sys_get_temp_dir(), 'fup_');
        file_put_contents($tmp, 'notvalid');

        $fakeUploaded = new class($tmp)
        {
            private $p;public function __construct($p)
            {$this->p = $p;}public function getRealPath()
            {return $this->p;}public function getClientOriginalExtension()
            {return 'docx';}public function extension()
            {return 'docx';}
        };

        $req = new class($fakeUploaded) extends Request
        {private $f;public function __construct($f)
            {$this->f = $f;}public function hasFile($key)
            {return true;}public function file($key = null, $default = null)
            {return $this->f;}};

        $facture = Facture::factory()->create();

        $res = $service->processUploadedFile($req, $facture);
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);

        @unlink($tmp);
    }
}
