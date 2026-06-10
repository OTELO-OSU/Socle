<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Entite;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeTags;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TagControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private MockHttpClient $mockHttpClient;
    private ?Plateforme $plateforme = null;

    private function cleanupDatabase(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_tags', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_historique_personnel', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_historique', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme', true));
        $conn->executeStatement($platform->getTruncateTableSQL('refresh_token', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_verification', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_machine', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription_oauth', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur', true));
        $conn->executeStatement($platform->getTruncateTableSQL('entite', true));
        $conn->executeStatement($platform->getTruncateTableSQL('categorie', true));
    }

    protected function setUp(): void
    {
        putenv('CENTRAL_TAG=http://central.test/tags');
        $_ENV['CENTRAL_TAG'] = 'http://central.test/tags';
        $_SERVER['CENTRAL_TAG'] = 'http://central.test/tags';

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->mockHttpClient = new MockHttpClient();
        static::getContainer()->set(HttpClientInterface::class, $this->mockHttpClient);

        $this->em = static::getContainer()->get(ManagerRegistry::class)->getManager();

        // Nettoyer avant chaque test pour éviter les doublons
        $this->cleanupDatabase();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $categorie = new Categorie();
        $categorie->setNom('Test Categorie');
        $this->em->persist($categorie);

        $unite = new Entite();
        $unite->setNom('Test Unite');
        $unite->setUniversite(false);
        $unite->setCategorie($categorie);
        $unite->setEmployeur(false);
        $unite->setLaboratoire(true);
        $unite->setEpic(false);
        $this->em->persist($unite);

        $user = new Utilisateur();
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setEmail('test@example.com');
        $user->setMdp($hasher->hashPassword($user, 'password'));
        $user->setAdmis(true);
        $user->setEmailValide(true);
        $user->setUnite($unite);
        $this->em->persist($user);

        $admin = new Utilisateur();
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setEmail('admin@example.com');
        $admin->setMdp($hasher->hashPassword($admin, 'password'));
        $admin->setAdmis(true);
        $admin->setEmailValide(true);
        $admin->setUnite($unite);
        $admin->setRoles(['ROLE_ADMIN']);
        $this->em->persist($admin);

        $this->plateforme = new Plateforme();
        $this->plateforme->setPseudoId('Test Plateforme');
        $this->em->persist($this->plateforme);

        $plateformeHisto = new PlateformeHistorique();
        $plateformeHisto->setNomOfficiel('Test Plateforme');
        $plateformeHisto->setPlateforme($this->plateforme);
        $plateformeHisto->setBrouillon(false);
        $plateformeHisto->setAnnee(2024);
        $this->em->persist($plateformeHisto);

        $this->em->flush();

        $this->mockCentralTags([
            ['id' => 1, 'nom' => 'TAG A'],
            ['id' => 2, 'nom' => 'TAG B'],
            ['id' => 3, 'nom' => 'TAG C'],
            ['id' => 4, 'nom' => 'TAG D'],
        ]);
    }

    private function mockCentralTags(array $tags, int $statusCode = 200): void
    {
        $responseBody = json_encode($tags, JSON_THROW_ON_ERROR);
        $this->mockHttpClient->setResponseFactory([
            new MockResponse($responseBody, ['http_code' => $statusCode]),
        ]);
    }

    private function mockCentralTransportFailure(string $message = 'Timeout'): void
    {
        $this->mockHttpClient->setResponseFactory(static function () use ($message): never {
            throw new TransportException($message);
        });
    }

    private function getUserAccessToken()
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:password')]
        );

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    private function getAdminAccessToken()
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('admin@example.com:password')]
        );

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();

        $this->em->clear();
        $this->plateforme = null;

        parent::tearDown();
    }

    public function testGetTagsSuccess(): void
    {
        $this->client->request('GET', '/api/tags');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(4, $data);
        $this->assertSame('TAG A', $data[0]['nom']);
    }

    public function testGetTagsRetourneErreurQuandCentralEnErreurHttp(): void
    {
        $this->mockCentralTags(['message' => 'boom'], 500);

        $this->client->request('GET', '/api/tags');

        $this->assertResponseStatusCodeSame(500);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Le service central des tags a retourne une erreur HTTP 500.', $data['message']);
    }

    public function testGetTagsRetourneErreurQuandCentralInjoignable(): void
    {
        $this->mockCentralTransportFailure();

        $this->client->request('GET', '/api/tags');

        $this->assertResponseStatusCodeSame(500);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('Impossible de joindre le service central des tags', $data['message']);
    }

    public function testGetPlateformeTagsSuccessAdmin(): void
    {
        $this->em->persist(new PlateformeTags($this->plateforme, 1));
        $this->em->persist(new PlateformeTags($this->plateforme, 3));
        $this->em->persist(new PlateformeTags($this->plateforme, 4));
        $this->em->flush();

        $this->client->request(
            'GET',
            '/api/plateforme/tags?id=' . $this->plateforme->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()]
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(3, $data);
        $this->assertSame([0, 1, 2], array_keys($data));
        $this->assertSame([1, 3, 4], array_column($data, 'id'));
    }

    public function testGetPlateformeTagsRefuseUtilisateurNonAutorise(): void
    {
        $this->client->request(
            'GET',
            '/api/plateforme/tags?id=' . $this->plateforme->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()]
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Accès refusé.', $data['message']);
    }

    public function testGetPlateformeTagsRetourne404SiPlateformeIntrouvable(): void
    {
        $this->client->request(
            'GET',
            '/api/plateforme/tags?id=999999',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()]
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Plateforme non trouvée.', $data['message']);
    }

    public function testPatchPlateformeTagsSuccessAjouteEtSupprime(): void
    {
        $plateformeId = $this->plateforme->getId();

        $this->em->persist(new PlateformeTags($this->plateforme, 1));
        $this->em->persist(new PlateformeTags($this->plateforme, 3));
        $this->em->flush();
        $this->em->clear();

        $this->client->request(
            'PATCH',
            '/api/plateforme/tags',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id' => $plateformeId,
                'tagIds' => [3, 4],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Tags modifiés avec succès.', $data['message']);

        $this->em->clear();
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        $saved = $this->em->getRepository(PlateformeTags::class)->findBy(['plateforme' => $plateforme]);
        $savedIds = array_map(static fn(PlateformeTags $tag) => $tag->getTagId(), $saved);
        sort($savedIds);
        $this->assertSame([3, 4], $savedIds);
    }

    public function testPatchPlateformeTagsRetourneErreurSiPlateformeIntrouvable(): void
    {
        $this->client->request(
            'PATCH',
            '/api/plateforme/tags',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id' => 999999,
                'tagIds' => [1, 2],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(500);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Plateforme non trouvée.', $data['message']);
    }

    public function testPatchPlateformeTagsRetourneErreurSiIdAbsent(): void
    {
        $this->client->request(
            'PATCH',
            '/api/plateforme/tags',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'tagIds' => [1, 2],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(500);
    }
}
