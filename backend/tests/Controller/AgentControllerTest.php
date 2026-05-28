<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Corps;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
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
        $unite->setUniversite(false);
        $this->em->persist($unite);

        $unite2 = new Entite();
        $unite2->setNom('Test Unite 2');
        $unite2->setCategorie($categorie);
        $unite2->setEmployeur(false);
        $unite2->setLaboratoire(true);
        $unite2->setUniversite(false);
        $this->em->persist($unite2);

        $uar = new Entite();
        $uar->setNom('Test Unite 2');
        $uar->setCategorie($categorie);
        $uar->setCodeUnite("UAR123");
        $uar->setEmployeur(false);
        $uar->setLaboratoire(true);
        $uar->setUniversite(false);
        $this->em->persist($uar);

        $employeur = new Entite();
        $employeur->setNom('Univlo');
        $employeur->setCategorie($categorie);
        $employeur->setEmployeur(true);
        $employeur->setLaboratoire(false);
        $employeur->setUniversite(true);
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

        $grade = new Grade();
        $grade->setNom('Test Grade');
        $this->em->persist($grade);

        $corps = new Corps();
        $corps->setNom('Test Corps');
        $corps->addGrade($grade);
        $this->em->persist($corps);

        $plateforme = new Plateforme();
        $plateforme->setPseudoId('Test Plateforme');
        $this->em->persist($plateforme);

        $plateformeHisto = new PlateformeHistorique();
        $plateformeHisto->setNomOfficiel('Test Plateforme');
        $plateformeHisto->setPlateforme($plateforme);
        $plateformeHisto->setBrouillon(false);
        $plateformeHisto->setAnnee(2024);
        $this->em->persist($plateformeHisto);

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Test TypeContrat');
        $this->em->persist($typeContrat);

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
            'grade' => $this->grade->getId(),
            'type_contrat' => $typeContrat->getId()
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

    public function testAccepterRefuserSoumissionMauvaisPayloadUtulisateurID(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'approbation' => true
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Champs requis manquants",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAccepterRefuserSoumissionMauvaisPayloadApprobation(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Champs requis manquants",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAccepterRefuserSoumissionMauvaisPayloadApprobationEnString(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => "refref"
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le champ approbation doit être un booléen.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAccepterSoumissionPasLesPerms(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => true
            ])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAccepterSoumissionPasLesPermsPourLesAutresUnites(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);

        $this->user->setUnite($this->unite2);
        $this->em->persist($utilisateur);
        $this->em->persist($this->user);
        $this->unite->setDirecteur($this->user);
        $this->em->persist($this->unite);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => true
            ])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires pour approuver cet utilisateur.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testAccepterSoumissionSucces(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);

        $this->user->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->persist($this->user);
        $this->unite->setDirecteur($this->user);
        $this->em->persist($this->unite);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => true
            ])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'utilisateur à bien été approuvé",
            $data['message']
        );

        $utilisateur = $this->em->find(Utilisateur::class, $utilisateur->getId());
        $this->assertFalse($utilisateur->isAttenteApprobationDU());
        $this->assertTrue($utilisateur->isAdmis());

        self::assertEmailCount(1);
        $message = self::getMailerMessage(0);
        $this->assertEmailHeaderSame($message, 'To', 'soumis@example.com');
    }

    public function testRefuserSoumissionPasLesPerms(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => false
            ])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testARefuserSoumissionPasLesPermsPourLesAutresUnites(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);

        $this->user->setUnite($this->unite2);
        $this->em->persist($utilisateur);
        $this->em->persist($this->user);
        $this->unite->setDirecteur($this->user);
        $this->em->persist($this->unite);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => false
            ])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Vous n'avez pas les droits nécessaires pour refuser cet utilisateur.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testRefuserSoumissionSucces(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);

        $this->user->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->persist($this->user);
        $this->unite->setDirecteur($this->user);
        $this->em->persist($this->unite);
        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/agent',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode([
                'utilisateurId' => $utilisateur->getId(),
                'approbation' => false
            ])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'utilisateur à bien été refusé",
            $data['message']
        );

        $utilisateur = $this->em->find(Utilisateur::class, $utilisateur->getId());
        $this->assertNull($utilisateur);

        self::assertEmailCount(0);
    }

    public function testGetEnAttenteDeSoumission(): void
    {
        $client = $this->client;

        //* Injecter un user en attente directement dans la base
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Soumis');
        $utilisateur->setPrenom('DU');
        $utilisateur->setEmail('soumis@example.com');
        $utilisateur->setAttenteApprobationDU(true);
        $utilisateur->setUnite($this->unite);

        $utilisateurUAR = new Utilisateur();
        $utilisateurUAR->setNom('Soumis');
        $utilisateurUAR->setPrenom('UAR');
        $utilisateurUAR->setEmail('soumis@uar.com');
        $utilisateurUAR->setAttenteApprobationDU(true);
        $utilisateurUAR->setUnite($this->uar);

        $this->user->setUnite($this->unite);
        $this->em->persist($utilisateur);
        $this->em->persist($utilisateurUAR);
        $this->em->persist($this->user);
        $this->unite->setDirecteur($this->user);
        $this->em->persist($this->unite);
        $this->em->flush();

        $client->request(
            'GET',
            '/api/agent/en-attente-du-du',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getUserAccessToken()],
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "soumis@example.com",
            $data[0]['email']
        );

        $client->request(
            'GET',
            '/api/agent/en-attente-du-du',
            [],
            [],
            ["HTTP_AUTHORIZATION" => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            2,
            count($data)
        );

        self::assertEmailCount(0);
    }
}
