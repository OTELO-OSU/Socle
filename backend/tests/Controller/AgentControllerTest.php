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
    private ?Utilisateur $admin = null;
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
        $unite->setEpic(false);
        $unite->setNom('Test Unite');
        $unite->setCategorie($categorie);
        $unite->setEmployeur(false);
        $unite->setLaboratoire(true);
        $unite->setUniversite(false);
        $this->em->persist($unite);

        $unite2 = new Entite();
        $unite2->setEpic(false);
        $unite2->setNom('Test Unite 2');
        $unite2->setCategorie($categorie);
        $unite2->setEmployeur(false);
        $unite2->setLaboratoire(true);
        $unite2->setUniversite(false);
        $this->em->persist($unite2);

        $uar = new Entite();
        $uar->setEpic(false);
        $uar->setNom('Test Unite 2');
        $uar->setCategorie($categorie);
        $uar->setCodeUnite("UAR123");
        $uar->setEmployeur(false);
        $uar->setLaboratoire(true);
        $uar->setUniversite(false);
        $this->em->persist($uar);

        $employeur = new Entite();
        $employeur->setEpic(false);
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
        $corps->setCompatibleCNU(true);
        $corps->setCompatibleBAP(false);
        $corps->setCompatibleSection(false);
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
        $this->admin = $admin;

        $this->payloadSoumissionDU = [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'mail@soumission.du',
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'baps_ids' => [],
            'emploi_types_ids' => [],
            'cnu' => null,
            'section' => null,
            'doctorant' => false
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
            static::getContainer()->get('translator')->trans('exception.permission.soumettre_au_du'),
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
            static::getContainer()->get('translator')->trans('exception.permission.soumettre_au_du'),
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
            static::getContainer()->get('translator')->trans('exception.bad_request.champs_manquants'),
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
            static::getContainer()->get('translator')->trans('exception.bad_request.champs_manquants'),
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
            static::getContainer()->get('translator')->trans('exception.bad_request.champs_approbation_boolean'),
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
            static::getContainer()->get('translator')->trans('exception.permission.gerer_utilisateur_soumission_du'),
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

        $this->em->persist($utilisateur);
        $this->unite2->setDirecteur($this->user);
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
            static::getContainer()->get('translator')->trans('exception.permission.gerer_utilisateur_soumission_du'),
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
            static::getContainer()->get('translator')->trans('exception.admin.utilisateur_approuve'),
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
            static::getContainer()->get('translator')->trans('exception.permission.gerer_utilisateur_soumission_du'),
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

        $this->user->getEntites()->clear();
        $this->user->getEntites()->add($this->unite2);
        $this->em->persist($utilisateur);
        $this->em->persist($this->user);
        $this->unite2->setDirecteur($this->user);
        $this->em->persist($this->unite2);
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
            static::getContainer()->get('translator')->trans('exception.permission.gerer_utilisateur_soumission_du'),
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
            static::getContainer()->get('translator')->trans('exception.admin.utilisateur_refuse'),
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

    public function testModifierAgentSoiMemeSucces(): void
    {
        $client = $this->client;

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'nom' => 'Test Modifié',
            'prenom' => 'User Modifié',
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => (new TypeContrat())->setNom('Test')->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload['type_contrat_id'] = $typeContrat->getId();

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('exception.admin.utilisateur_modifie'), $data['message']);
        $this->assertSame('Test Modifié', $data['utilisateur']['nom']);

        self::assertEmailCount(0);
    }

    public function testModifierAgentSoiMemeEmailChange(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => 'newemail@example.com',
            'nom' => $this->user->getNom(),
            'prenom' => $this->user->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('exception.admin.utilisateur_modifie'), $data['message']);
        $this->assertSame('test@example.com', $data['utilisateur']['email']);

        $userRefreshed = $this->em->find(Utilisateur::class, $this->user->getId());
        $this->assertSame('test@example.com', $userRefreshed->getEmail());

        self::assertEmailCount(0);
    }

    public function testModifierAgentAutreUtilisateurSansPerm(): void
    {
        $client = $this->client;

        $autreUser = new Utilisateur();
        $autreUser->setNom('Autre');
        $autreUser->setPrenom('User');
        $autreUser->setEmail('autre@example.com');
        $autreUser->setMdp('password');
        $autreUser->setAdmis(true);
        $autreUser->setEmailValide(true);
        $autreUser->setUnite($this->unite);
        $this->em->persist($autreUser);
        $this->em->flush();

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $autreUser->getId(),
            'email' => $autreUser->getEmail(),
            'nom' => 'Modifié',
            'prenom' => 'User',
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.permission.modifier_utilisateur'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAgentAdminChangeEmail(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => 'newemail@example.com',
            'nom' => $this->user->getNom(),
            'prenom' => $this->user->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('exception.admin.utilisateur_modifie'), $data['message']);
        $this->assertSame('newemail@example.com', $data['utilisateur']['email']);

        self::assertEmailCount(0);
    }

    public function testModifierAdminEnTantQueDORefuser(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $user = $this->em->find(Utilisateur::class, $this->user->getId());
        $user->setRoles(['ROLE_DIR_OSU']);
        $this->em->persist($user);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->admin->getId(),
            'email' => $this->admin->getEmail(),
            'nom' => $this->admin->getNom(),
            'prenom' => $this->admin->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.permission.modifier_utilisateur'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAdminEnTantQueDODRefuser(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $user = $this->em->find(Utilisateur::class, $this->user->getId());
        $user->setRoles(['ROLE_DIR_OSU_DELEGUE']);
        $this->em->persist($user);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->admin->getId(),
            'email' => $this->admin->getEmail(),
            'nom' => $this->admin->getNom(),
            'prenom' => $this->admin->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.permission.modifier_utilisateur'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierDOEnTantQueDODRefuser(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $user = $this->em->find(Utilisateur::class, $this->user->getId());
        $user->setRoles(['ROLE_DIR_OSU_DELEGUE']);
        $admin = $this->em->find(Utilisateur::class, $this->admin->getId());
        $admin->setRoles(['ROLE_DIR_OSU']);
        $this->em->persist($user);
        $this->em->persist($admin);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->admin->getId(),
            'email' => $this->admin->getEmail(),
            'nom' => $this->admin->getNom(),
            'prenom' => $this->admin->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.permission.modifier_utilisateur'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAgentChampsManquants(): void
    {
        $client = $this->client;

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('exception.bad_request.champs_manquants'), $data['message']);

        self::assertEmailCount(0);
    }

    public function testModifierAgentUtilisateurIntrouvable(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => 999999,
            'email' => 'test@example.com',
            'nom' => 'Test',
            'prenom' => 'User',
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.not_found.utilisateur'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAgentUniteInvalide(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'nom' => $this->user->getNom(),
            'prenom' => $this->user->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => 999999,
            'type_contrat_id' => $typeContrat->getId(),
            'corps_id' => $this->corps->getId(),
            'grade_id' => $this->grade->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.not_found.unite'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAgentCorpsManquantPourNonDoctorant(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'nom' => $this->user->getNom(),
            'prenom' => $this->user->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'doctorant' => false,
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            static::getContainer()->get('translator')->trans('exception.bad_request.corps_obligatoire'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testModifierAgentDoctorantSucces(): void
    {
        $client = $this->client;

        $typeContrat = new TypeContrat();
        $typeContrat->setNom('Type Contrat Test');
        $this->em->persist($typeContrat);
        $this->em->flush();

        $payload = [
            'utilisateur_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'nom' => $this->user->getNom(),
            'prenom' => $this->user->getPrenom(),
            'employeur_id' => $this->employeur->getId(),
            'unite_id' => $this->unite->getId(),
            'type_contrat_id' => $typeContrat->getId(),
            'doctorant' => true,
            'universite_rattachement_id' => $this->employeur->getId(),
        ];

        $client->request(
            'PUT',
            '/api/agent',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(static::getContainer()->get('translator')->trans('exception.admin.utilisateur_modifie'), $data['message']);
        $this->assertTrue($data['utilisateur']['doctorant']);

        self::assertEmailCount(0);
    }
}
