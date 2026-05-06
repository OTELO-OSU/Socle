<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Corps;
use App\Entity\CorpsGrade;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AgentControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;
    private ?Plateforme $testPlateforme = null;
    private ?PlateformeHistorique $plateformeHisto = null;
    private ?Utilisateur $user = null;
    private ?Entite $unite = null;
    private ?Entite $unite2 = null;
    private ?Entite $uar = null;
    private ?Entite $employeur = null;
    private ?Corps $corps = null;
    private ?Grade $grade = null;

    private ?array $payloadSoumissionDU = null;

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
        $unite->setCategorie($categorie);
        $unite->setEmployeur(false);
        $unite->setLaboratoire(true);
        $this->em->persist($unite);

        $unite2 = new Entite();
        $unite2->setNom('Test Unite 2');
        $unite2->setCategorie($categorie);
        $unite2->setEmployeur(false);
        $unite2->setLaboratoire(true);
        $this->em->persist($unite2);

        $uar = new Entite();
        $uar->setNom('Test Unite 2');
        $uar->setCategorie($categorie);
        $uar->setCodeUnite("UAR123");
        $uar->setEmployeur(false);
        $uar->setLaboratoire(true);
        $this->em->persist($uar);

        $employeur = new Entite();
        $employeur->setNom('Univlo');
        $employeur->setCategorie($categorie);
        $employeur->setEmployeur(true);
        $employeur->setLaboratoire(false);
        $this->em->persist($employeur);

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

        $unite->setDirecteur($admin);
        $this->em->persist($unite);

        $corps = new Corps();
        $corps->setNom('Test Corps');
        $this->em->persist($corps);

        $grade = new Grade();
        $grade->setNom('Test Grade');
        $this->em->persist($grade);

        $corpsGrade = new CorpsGrade();
        $corpsGrade->setCorps($corps);
        $corpsGrade->setGrade($grade);
        $this->em->persist($corpsGrade);

        $plateforme = new Plateforme();
        $plateforme->setPseudoId('Test Plateforme');
        $this->em->persist($plateforme);

        $plateformeHisto = new PlateformeHistorique();
        $plateformeHisto->setNomOfficiel('Test Plateforme');
        $plateformeHisto->setPlateforme($plateforme);
        $plateformeHisto->setBrouillon(false);
        $plateformeHisto->setAnnee(2024);
        $this->em->persist($plateformeHisto);

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
        $this->unite2 = $unite2;
        $this->uar = $uar;
        $this->employeur = $employeur;
        $this->user = $user;
        $this->corps = $corps;
        $this->grade = $grade;
        $this->plateformeHisto = $plateformeHisto;

        $this->payloadSoumissionDU = [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'mail@soumission.du',
            'employeur' => $this->employeur->getId(),
            'unite' => $this->unite->getId(),
            'corps' => $this->corps->getId(),
            'grade' => $this->grade->getId()
        ];
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

    public function testSoumissionDUNonAutorise(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode($this->payloadSoumissionDU)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testSoumissionDUNonAutorisePourUniteAutre(): void
    {
        $client = $this->client;

        //* Mettre le user en tant que respo d'une plateforme + mettre le user dans une unité différente de celle du soumis
        $this->user->setUnite($this->unite2);
        $this->em->persist($this->user);

        $personnel = new PlateformeHistoriquePersonnel();
        $personnel->setUtilisateur($this->user);
        $personnel->setPlateformeHistorique($this->plateformeHisto);
        $personnel->setResponsableScientifique(true);
        $this->em->persist($personnel);

        $this->em->flush();

        $client->request(
            'POST',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode($this->payloadSoumissionDU)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires pour créer un utilisateur dans cette unité.",
            $data['message']
        );

        self::assertEmailCount(0);
        // $message = self::getMailerMessage(0);
        // $this->assertEmailHeaderSame($message, 'To', 'test@example.com');
    }

    public function testSoumissionDUSuccessMemeUnite(): void
    {
        $client = $this->client;

        //* Mettre le user en tant que respo d'une plateforme + mettre le user dans une unité différente de celle du soumis
        $this->user->setUnite($this->unite);
        $this->em->persist($this->user);

        $personnel = new PlateformeHistoriquePersonnel();
        $personnel->setUtilisateur($this->user);
        $personnel->setPlateformeHistorique($this->plateformeHisto);
        $personnel->setResponsableScientifique(true);
        $this->em->persist($personnel);

        $this->em->flush();

        $client->request(
            'POST',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode($this->payloadSoumissionDU)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Soumission envoyée avec succès",
            $data['message']
        );
        $this->assertArrayHasKey('utilisateur', $data);
        $this->assertIsInt($data['utilisateur']['id']);

        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'admin@example.com');
    }

    public function testSoumissionDUSuccessFromUAR(): void
    {
        $client = $this->client;

        //* Mettre le user en tant que respo d'une plateforme + mettre le user dans une unité différente de celle du soumis
        $this->user->setUnite($this->uar);
        $this->em->persist($this->user);

        $personnel = new PlateformeHistoriquePersonnel();
        $personnel->setUtilisateur($this->user);
        $personnel->setPlateformeHistorique($this->plateformeHisto);
        $personnel->setResponsableScientifique(true);
        $this->em->persist($personnel);

        $this->em->flush();

        $client->request(
            'POST',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode($this->payloadSoumissionDU)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Soumission envoyée avec succès",
            $data['message']
        );
        $this->assertArrayHasKey('utilisateur', $data);
        $this->assertIsInt($data['utilisateur']['id']);

        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'admin@example.com');
    }
}
