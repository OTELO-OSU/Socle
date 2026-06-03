<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Entite;
use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SaisieControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?Plateforme $testPlateforme = null;
    private ?PlateformeHistorique $plateformeHisto = null;
    private ?Utilisateur $user = null;
    private ?Entite $unite = null;

    private function cleanupDatabase(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('parametre', true));
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
        $this->client = static::createClient();
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

        $this->testPlateforme = new Plateforme();
        $this->testPlateforme->setPseudoId('Test Plateforme');
        $this->em->persist($this->testPlateforme);

        $this->plateformeHisto = new PlateformeHistorique();
        $this->plateformeHisto->setNomOfficiel('Test Plateforme');
        $this->plateformeHisto->setPlateforme($this->testPlateforme);
        $this->plateformeHisto->setBrouillon(false);
        $this->plateformeHisto->setAnnee(2024);
        $this->em->persist($this->plateformeHisto);

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

    public function testGetSaisieInterditPourUtilisateurSansRole(): void
    {
        $client = $this->client;

        $client->request(
            'GET',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()]
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );
    }

    public function testGetSaisieSuccesPourAdmin(): void
    {
        $parametre = new Parametre();
        $parametre->setCle('annee_saisie_en_cours');
        $parametre->setValeur('2025');
        $this->em->persist($parametre);
        $this->em->flush();

        $client = $this->client;
        $client->request(
            'GET',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()]
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('2025', $data['annee']);
    }

    public function testPatchSaisieSansAnnee(): void
    {
        $client = $this->client;
        $client->request(
            'PATCH',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'année de saisie est requise.",
            $data['message']
        );
    }

    public function testPatchSaisieFormatAnneeInvalide(): void
    {
        $client = $this->client;
        $client->request(
            'PATCH',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['annee' => '25'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'année de saisie doit être au format YYYY.",
            $data['message']
        );
    }

    public function testPatchSaisieInterditPourUtilisateurSansRole(): void
    {
        $client = $this->client;
        $client->request(
            'PATCH',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode(['annee' => '2027'])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits.",
            $data['message']
        );
    }

    public function testPatchSaisieSuccesPourAdmin(): void
    {
        $client = $this->client;
        $client->request(
            'PATCH',
            '/api/saisie',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode(['annee' => '2028'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'année de saisie a été changée.",
            $data['message']
        );

        $anneeSaisie = $this->em->getRepository(Parametre::class)->findOneBy(['cle' => 'annee_saisie_en_cours']);
        $this->assertNotNull($anneeSaisie);
        $this->assertEquals('2028', $anneeSaisie->getValeur());
    }
}
