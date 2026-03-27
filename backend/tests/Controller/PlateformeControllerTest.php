<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Entite;
use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeEditeur;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PlateformeControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;

    private function cleanupDatabase(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('refresh_token', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_verification', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_machine', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription_oauth', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_historique', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme', true));
        $conn->executeStatement($platform->getTruncateTableSQL('parametre', true));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(ManagerRegistry::class)->getManager();

        // Nettoyer avant chaque test pour éviter les doublons
        $this->cleanupDatabase();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $anneeSaisie = new Parametre();
        $anneeSaisie->setCle("annee_saisie_en_cours");
        $anneeSaisie->setValeur("2025");
        $this->em->persist($anneeSaisie);

        $user = new Utilisateur();
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setEmail('test@example.com');
        $user->setMdp($hasher->hashPassword($user, 'password'));
        $user->setAdmis(true);
        $user->setEmailValide(true);
        $this->em->persist($user);

        $admin = new Utilisateur();
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setEmail('admin@example.com');
        $admin->setMdp($hasher->hashPassword($admin, 'password'));
        $admin->setAdmis(true);
        $admin->setEmailValide(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $this->em->persist($admin);

        $this->em->flush();
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

    protected function tearDown(): void
    {
        $this->cleanupDatabase();

        parent::tearDown();
    }

    public function testcreatePlateformeNonAdmin(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme/creer',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits pour créer une plateforme",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testcreatePlateformeSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme/creer',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom officiel de la plateforme est requis.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testcreatePlateformeSansAnneeDeSaisie(): void
    {
        $client = $this->client;

        $annee = $this->em->getRepository(Parametre::class)->findOneBy(["cle" => "annee_saisie_en_cours"]);
        $this->em->remove($annee);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/plateforme/creer',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Aucune année de saisie n'a été trouvée.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testcreatePlateformeSuccess(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme/creer',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme créée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);
    }
}
