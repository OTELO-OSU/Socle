<?php

namespace App\Tests\Controller;

use App\Service\ExcelService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExcelControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testExportToutesLesPlateformesRetourneUnFichierExcel(): void
    {
        $excelService = $this->createMock(ExcelService::class);
        $excelService
            ->expects($this->once())
            ->method('exportAllPlatformsToExcel')
            ->willReturn('xlsx-content');

        static::getContainer()->set(ExcelService::class, $excelService);

        $this->client->request('GET', '/api/export/plateformes');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('xlsx-content', $this->client->getResponse()->getContent());
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $this->client->getResponse()->headers->get('Content-Type')
        );
        $this->assertSame(
            'attachment; filename="plateformes.xlsx"',
            $this->client->getResponse()->headers->get('Content-Disposition')
        );
    }

    public function testExportPlateformesParTagTransmetLeTag(): void
    {
        $excelService = $this->createMock(ExcelService::class);
        $excelService
            ->expects($this->once())
            ->method('exportPlatformsByTagToExcel')
            ->with(17)
            ->willReturn('xlsx-content');
        static::getContainer()->set(ExcelService::class, $excelService);

        $this->client->request('GET', '/api/export/plateformes/tag?tagId=17');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('xlsx-content', $this->client->getResponse()->getContent());
    }

    public function testExportPlateformesParUniteTransmetLeRorId(): void
    {
        $excelService = $this->createMock(ExcelService::class);
        $excelService
            ->expects($this->once())
            ->method('exportPlatformsByUnitRorToExcel')
            ->with('2')
            ->willReturn('xlsx-content');
        static::getContainer()->set(ExcelService::class, $excelService);

        $this->client->request('GET', '/api/export/plateformes/unite?laboId=2');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('xlsx-content', $this->client->getResponse()->getContent());
    }

    public function testExportPlateformesParTagRejetteUnIdAbsent(): void
    {
        $this->client->request('GET', '/api/export/plateformes/tag');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testExportPlateformesParUniteRejetteUnRorAbsent(): void
    {
        $this->client->request('GET', '/api/export/plateformes/unite');

        $this->assertResponseStatusCodeSame(400);
    }
}
