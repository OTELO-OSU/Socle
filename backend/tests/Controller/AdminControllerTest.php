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

class AdminControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?Plateforme $testPlateforme = null;
    private ?PlateformeHistorique $plateformeHisto = null;
    private ?Utilisateur $user = null;

    private function cleanupDatabase(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_historique_personnel', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme_historique', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme', true));
        $conn->executeStatement($platform->getTruncateTableSQL('refresh_token', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_verification', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_machine', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur_inscription_oauth', true));
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur', true));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(ManagerRegistry::class)->getManager();

        // Nettoyer avant chaque test pour éviter les doublons
        $this->cleanupDatabase();

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new Utilisateur();
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setEmail('test@example.com');
        $user->setMdp($hasher->hashPassword($user, 'password'));
        $user->setAdmis(true);
        $user->setEmailValide(true);
        $this->em->persist($user);

        $this->user = $user;

        $admin = new Utilisateur();
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setEmail('admin@example.com');
        $admin->setMdp($hasher->hashPassword($admin, 'password'));
        $admin->setAdmis(true);
        $admin->setEmailValide(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $this->em->persist($admin);

        $categorie = new Categorie();
        $categorie->setNom('Test Categorie');
        $this->em->persist($categorie);

        $entite = new Entite();
        $entite->setNom('Test Entite');
        $entite->setCategorie($categorie);
        $entite->setEmployeur(true);
        $entite->setLaboratoire(false);
        $this->em->persist($entite);

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

    public function testAdmissionSansMail(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/utilisateurs/approuver',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('admin@example.com:password')]
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'email est requis.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAdmissionFauxMail(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/utilisateurs/approuver',
            [],
            [],
            [],
            \json_encode(['email' => 'invalid-email'])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur non trouvé.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testRefusFauxMail(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/utilisateurs/refuser',
            [],
            [],
            [],
            \json_encode(['email' => 'invalid-email'])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur non trouvé.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAdmissionNonAdmin(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/utilisateurs/approuver',
            [],
            [],
            [],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits pour approuver cet utilisateur",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testRefusNonAdmin(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/utilisateurs/refuser',
            [],
            [],
            [],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits pour refuser cet utilisateur",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAdmissionEntiteDemande(): void
    {
        $client = $this->client;

        $userInscription = new UtilisateurInscription();
        $userInscription->setUniteDemande($this->em->getRepository(Entite::class)->findOneBy([]));
        $userInscription->setOsuDemande(false);
        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false)->setUtilisateurInscription($userInscription);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/utilisateurs/approuver',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur approuvé avec succès.",
            $data['message']
        );

        $this->assertTrue($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->isAdmis());
        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'test@example.com');
        $this->assertEquals(
            $this->em->getRepository(Entite::class)->findOneBy([])->getDirecteur()->getEmail(),
            "test@example.com"
        );
    }

    public function testAdmissionPlateformeDemande(): void
    {
        $client = $this->client;

        $userInscription = new UtilisateurInscription();
        $userInscription->setPlateformeDemande($this->testPlateforme);
        $userInscription->setOsuDemande(false);
        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false)->setUtilisateurInscription($userInscription);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/utilisateurs/approuver',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur approuvé avec succès.",
            $data['message']
        );

        $this->assertTrue($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->isAdmis());
        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'test@example.com');

        // Recharger la plateforme depuis la base avec son historique et ses personnels
        $refreshedPlateforme = $this->em->getRepository(Plateforme::class)->find($this->testPlateforme->getId());
        $this->assertEquals(
            $refreshedPlateforme->getLastHistorique()->getPlateformeHistoriquePersonnels()[0]->getUtilisateurId()->getEmail(),
            "test@example.com"
        );
    }

    public function testRefusUtilisateurSuccess(): void
    {
        $client = $this->client;

        $userInscription = new UtilisateurInscription();
        $userInscription->setPlateformeDemande($this->testPlateforme);
        $userInscription->setOsuDemande(false);
        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false)->setUtilisateurInscription($userInscription);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/utilisateurs/refuser',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Utilisateur refusé avec succès.",
            $data['message']
        );

        $this->assertNull($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']));
        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'test@example.com');
    }

    public function testAffiliationPlateformeIdPlateformeAbsent(): void
    {
        $client = $this->client;

        // Récupérer l'ID de la plateforme créée dans le setUp
        $plateformeId = $this->testPlateforme->getId();

        $client->request(
            'PATCH',
            '/api/plateforme/responsables',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'ID de la plateforme est requis.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAffiliationPlateformePlateformeIdInexistant(): void
    {
        $client = $this->client;

        $client->request(
            'PATCH',
            '/api/plateforme/responsables',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['plateformeId' => -1, 'email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La plateforme n'existe pas.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAffiliationPlateformeNonAdmin(): void
    {
        $client = $this->client;

        // Récupérer l'ID de la plateforme créée dans le setUp
        $plateformeId = $this->testPlateforme->getId();

        $client->request(
            'PATCH',
            '/api/plateforme/responsables',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode(['email' => 'test@example.com', 'plateformeId' => $plateformeId])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les permissions nécessaires pour modifier les responsables de la plateforme.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAffiliationPlateformeSuccess(): void
    {
        $client = $this->client;

        // Récupérer l'ID de la plateforme créée dans le setUp
        $plateformeId = $this->plateformeHisto->getId();

        $client->request(
            'PATCH',
            '/api/plateforme/responsables',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['responsableScientifiquesIds' => [$this->user->getId()], 'plateformeId' => $plateformeId])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Responsables de la plateforme mis à jour avec succès.",
            $data['message']
        );

        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'test@example.com');
    }

    public function testGetUtilisateurEnAttenteDeValidationError(): void
    {
        $client = $this->client;

        $client->request(
            'GET',
            '/api/utilisateurs/attente-de-validation',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits pour voir les utilisateurs en attente de validation",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testGetUtilisateurEnAttenteDeValidationSuccess(): void
    {
        $client = $this->client;

        $us = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $us->setAdmis(false);
        $ui = new UtilisateurInscription();
        $ui->setOsuDemande(true);
        $us->setUtilisateurInscription($ui);
        $this->em->flush();

        $client->request(
            'GET',
            '/api/utilisateurs/attente-de-validation',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "test@example.com",
            $data['0']['email']
        );
        $this->assertEquals(
            "Test",
            $data['0']['nom']
        );
        $this->assertEquals(
            "User",
            $data['0']['prenom']
        );
        $this->assertNull(
            $data['0']['plateformeDemande']
        );
        $this->assertNull(
            $data['0']['uniteDemande']
        );
        $this->assertTrue(
            $data['0']['osuDemande']
        );

        self::assertEmailCount(0);
    }
}
