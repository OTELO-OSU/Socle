<?php

namespace App\Tests\Controller;

use App\Entity\Plateforme;
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

        $this->client->request('POST', '/api/harvester/hmt/create?clientId=client-123');

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

    public function testGetListePlateformeHarvesterRetourne400QuandParametresManquants(): void
    {
        $this->client->request('GET', '/api/hmt/plateformes?clientId=test-client');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('harvester.exception.client_id_secret_id_obligatoire'), $data['message']);
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

        $this->client->request('GET', '/api/hmt/plateformes?clientId=client-123&clientSecret=secret-456');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(2, $data[1]['id']);
    }

    public function testGetDetailPlateformeHarvesterRetourne400QuandParametresManquants(): void
    {
        $this->client->request('GET', '/api/hmt/plateforme?id=1&clientId=client-123');

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

        $this->client->request('GET', '/api/hmt/plateforme?plateformeId=1&clientId=client-123&clientSecret=secret-456');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("ptf_1", $data['pseudoId']);
        $this->assertSame('Plateforme A', $data['dernier_historique']['nom_officiel']);
    }
}
