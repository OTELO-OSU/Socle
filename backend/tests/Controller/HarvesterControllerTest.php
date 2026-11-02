<?php

namespace App\Tests\Controller;

use App\Entity\Plateforme;
use App\Service\ExcelService;
use App\Service\HarvesterService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HarvesterControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetHMTRetourne400QuandClientIdAbsent(): void
    {
        $this->client->request('POST', '/api/harvester/hmt/create');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('harvester.succes.client_id_obligatoire'), $data['message']);
    }

    public function testGetHMTRetourne200AvecHmt(): void
    {
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('creerHMT')
            ->with('client-123')
            ->willReturn(['hmt' => 'abc.def.ghi']);

        static::getContainer()->set(HarvesterService::class, $harvesterService);

        $this->client->request('POST', '/api/harvester/hmt/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['clientId' => 'client-123']));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('abc.def.ghi', $data['hmt']);
    }

    public function testPostHMTRetourne400QuandHmtAbsent(): void
    {
        $this->client->request(
            'POST',
            '/api/harvester/hmt',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('harvester.succes.hmt_obligatoire'), $data['message']);
    }

    public function testPostHMTRetourne200QuandHmtValide(): void
    {
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('ajoutHMT')
            ->with('abc.def.ghi');

        static::getContainer()->set(HarvesterService::class, $harvesterService);

        $this->client->request(
            'POST',
            '/api/harvester/hmt',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['hmt' => 'abc.def.ghi'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('harvester.succes.hmt_ajoute'), $data['message']);
    }

    public function testGetListePlateformeHarvesterRetourne200AvecListe(): void
    {
        $plateformes = [
            ['id' => 1, 'nomOfficiel' => 'Plateforme A'],
            ['id' => 2, 'nomOfficiel' => 'Plateforme B'],
        ];

        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getPlateformeListePourMachine')
            ->with('client-123', 'secret-456')
            ->willReturn($plateformes);

        static::getContainer()->set(HarvesterService::class, $harvesterService);
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Basic ' . base64_encode('client-123:secret-456'));
        $this->client->request('GET', '/api/hmt/plateformes');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(2, $data[1]['id']);
    }

    public function testGetDetailPlateformeHarvesterRetourne400QuandParametresManquants(): void
    {
        $headerAuth = 'Basic ' . base64_encode('test-client:test-secret');
        $this->client->setServerParameter('HTTP_AUTHORIZATION', $headerAuth);
        $this->client->request('GET', '/api/hmt/plateforme?id=1');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('harvester.exception.client_id_secret_id_obligatoire_plateforme_id'), $data['message']);
    }

    public function testGetDetailPlateformeHarvesterRetourne200AvecDetail(): void
    {
        $plateforme = new Plateforme();
        $plateforme->setPseudoId("ptf_1");
        $plateforme->addPlateformeHistorique((new \App\Entity\PlateformeHistorique())->setNomOfficiel('Plateforme A'));

        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getPlateformeDetailPourMachine')
            ->with('client-123', 'secret-456', 1)
            ->willReturn($plateforme);

        static::getContainer()->set(HarvesterService::class, $harvesterService);

        $headerAuth = 'Basic ' . base64_encode('client-123:secret-456');
        $this->client->request('GET', '/api/hmt/plateforme?plateformeId=1', [], [], ['HTTP_AUTHORIZATION' => $headerAuth]);

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("ptf_1", $data['pseudoId']);
        $this->assertSame('Plateforme A', $data['dernier_historique']['nom_officiel']);
    }

    public function testExportPlateformeHarvesterHMTRetourneLesDonneesAvecReferencesResolues(): void
    {
        $plateforme = new Plateforme();
        $exportData = [
            'platform' => ['id' => 1, 'dernier_historique' => ['ville_id' => 4]],
            'references' => ['ville' => ['4' => 'Paris (75 Paris)']],
        ];
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getPlateformeDetailPourMachine')
            ->with('client-123', 'secret-456', 1)
            ->willReturn($plateforme);

        $excelService = $this->createMock(ExcelService::class);
        $excelService
            ->expects($this->once())
            ->method('preparePlatformePourExport')
            ->with($plateforme)
            ->willReturn($exportData);

        static::getContainer()->set(HarvesterService::class, $harvesterService);
        static::getContainer()->set(ExcelService::class, $excelService);

        $headerAuth = 'Basic ' . base64_encode('client-123:secret-456');
        $this->client->request(
            'GET',
            '/api/hmt/plateforme/export-data?plateformeId=1',
            [],
            [],
            ['HTTP_AUTHORIZATION' => $headerAuth]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame($exportData, json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testExportPlateformeHarvesterHMTAllContourneLaPermissionParTags(): void
    {
        $plateforme = new Plateforme();
        $exportData = [
            'platform' => ['id' => 1, 'dernier_historique' => ['ville_id' => 4]],
            'references' => ['ville' => ['4' => 'Paris (75 Paris)']],
        ];
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getPlateformeDetailPourMachineSansPermission')
            ->with('client-123', 'secret-456', 1)
            ->willReturn($plateforme);

        $excelService = $this->createMock(ExcelService::class);
        $excelService
            ->expects($this->once())
            ->method('preparePlatformePourExport')
            ->with($plateforme)
            ->willReturn($exportData);

        static::getContainer()->set(HarvesterService::class, $harvesterService);
        static::getContainer()->set(ExcelService::class, $excelService);

        $this->client->request(
            'GET',
            '/api/hmt/plateforme/export-data-all?plateformeId=1',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('client-123:secret-456')]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame($exportData, json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testListePlateformesPourExportTransmetLeFiltreParTag(): void
    {
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getToutesLesPlateformesPourMachine')
            ->with('client-123', 'secret-456', 17, null)
            ->willReturn([]);
        static::getContainer()->set(HarvesterService::class, $harvesterService);

        $this->client->request(
            'GET',
            '/api/hmt/plateformes/export?tagId=17',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('client-123:secret-456')]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testListePlateformesPourExportTransmetLeFiltreParRor(): void
    {
        $harvesterService = $this->createMock(HarvesterService::class);
        $harvesterService
            ->expects($this->once())
            ->method('getToutesLesPlateformesPourMachine')
            ->with('client-123', 'secret-456', null, '03yrm5c26')
            ->willReturn([]);
        static::getContainer()->set(HarvesterService::class, $harvesterService);

        $this->client->request(
            'GET',
            '/api/hmt/plateformes/export?laboId=03yrm5c26',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('client-123:secret-456')]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame([], json_decode($this->client->getResponse()->getContent(), true));
    }
}
