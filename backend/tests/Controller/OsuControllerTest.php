<?php

namespace App\Tests\Controller;

use App\Entity\Entite;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OsuControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?Utilisateur $user = null;
    private ?Utilisateur $user2 = null;
    private ?Entite $unite = null;

    private function cleanupDatabase(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur', true));
        $conn->executeStatement($platform->getTruncateTableSQL('entite', true));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(ManagerRegistry::class)->getManager();

        // Nettoyer avant chaque test pour éviter les doublons
        $this->cleanupDatabase();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $unite = new Entite();
        $unite->setNom('Test Unite');
        $unite->setUniversite(false);
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

        $user2 = new Utilisateur();
        $user2->setNom('Test');
        $user2->setPrenom('User');
        $user2->setEmail('test2@example.com');
        $user2->setMdp($hasher->hashPassword($user2, 'password'));
        $user2->setAdmis(true);
        $user2->setEmailValide(true);
        $user2->setUnite($unite);
        $this->em->persist($user2);
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

        $this->em->flush();
        $this->unite = $unite;
        $this->user = $user;
        $this->user2 = $user2;
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

    private function getUser2AccessToken()
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test2@example.com:password')]
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

        parent::tearDown();
    }

    public function testChangerDUMauvaisId(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['directeur_osu_id' => -7])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur non trouvé.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDOInterdit(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode(['directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDOSuccess(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le directeur de l'OSU a été changé.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDOSuicide(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->find($this->user->getId())->setRoles(array_merge($this->user->getRoles(), ['ROLE_DIR_OSU']));
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/osu/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode(['directeur_osu_id' => $this->user2->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDOSuicideParDelegation(): void
    {
        $this->testChangerDOSuccess();
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode(['directeur_osu_delegue_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueMauvaisBody(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Opération invalide. L'opération doit être 'add' ou 'remove'.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueMauvaisBodyOperation(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['directeur_osu_delegue_id' => $this->user->getId(), 'operation' => "UUUUUUUUU"])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Opération invalide. L'opération doit être 'add' ou 'remove'.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueMauvaisBodyUtilisateur(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['operation' => "add"])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'ID de l'utilisateur est requis.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueInterdit(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUser2AccessToken()],
            json_encode(['operation' => "add", 'delegue_directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueParAdmin(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['operation' => "add", 'delegue_directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le délégué du directeur de l'OSU a été ajouté.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueParDO(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->find($this->user2->getId())->setRoles(array_merge($this->user->getRoles(), ['ROLE_DIR_OSU']));
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUser2AccessToken()],
            json_encode(['operation' => "add", 'delegue_directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le délégué du directeur de l'OSU a été ajouté.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAjouterDelegueParDODInterdit(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->find($this->user2->getId())->setRoles(array_merge($this->user->getRoles(), ['ROLE_DIR_OSU_DELEGUE']));
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/osu/directeur/delegue',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUser2AccessToken()],
            json_encode(['operation' => "add", 'delegue_directeur_osu_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );

        self::assertEmailCount(0);
    }
}
