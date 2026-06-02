<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Entite;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UniteControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?Utilisateur $user = null;
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

        parent::tearDown();
    }

    public function testChangerDUPasDeUtilisateurId(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['unite_id' => $this->unite->getId()])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Champs requis manquants",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDUPasDeUniteId(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['utilisateur_id' => $this->user->getId()])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Champs requis manquants",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerInterdit(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode(['utilisateur_id' => $this->user->getId(), 'unite_id' => $this->unite->getId()])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDUUniteInconnue(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['utilisateur_id' => $this->user->getId(), 'unite_id' => -1])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'unité spécifiée est invalide.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDUUserInconnu(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['utilisateur_id' => -1, 'unite_id' => $this->unite->getId()])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'utilisateur spécifié est invalide.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDUSuccesAUser(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['utilisateur_id' => $this->user->getId(), 'unite_id' => $this->unite->getId()])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Directeur de l'unité modifié avec succès",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testChangerDUSuccesSansUser(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/unite/directeur',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['utilisateur_id' => null, 'unite_id' => $this->unite->getId()])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Directeur de l'unité modifié avec succès",
            $data['message']
        );

        self::assertEmailCount(0);
    }
}
