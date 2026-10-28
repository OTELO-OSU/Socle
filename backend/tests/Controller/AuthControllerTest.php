<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Entite;
use App\Entity\Plateforme;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscriptionOAuth;
use App\Entity\UtilisateurVerification;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class AuthControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $em;

    private function assertVerificationEmailSentTo(string $recipient): void
    {
        self::assertEmailCount(1);

        $message = self::getMailerMessage(0);
        $this->assertNotNull($message);
        self::assertEmailAddressContains($message, 'to', $recipient);
        self::assertEmailSubjectContains($message, 'Vérification de votre adresse email');
        self::assertEmailTextBodyContains($message, 'Veuillez cliquer sur le bouton pour vérifier votre adresse email.');
        self::assertEmailHtmlBodyContains($message, 'verification-email?token=');
    }

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

        $entite = new Entite();
        $entite->setEpic(false);
        $entite->setNom('Test Entite');
        $entite->setCategorie($categorie);
        $entite->setEmployeur(false);
        $entite->setLaboratoire(true);
        $entite->setUniversite(false);
        $this->em->persist($entite);

        $user = new Utilisateur();
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setEmail('test@example.com');
        $user->setMdp($hasher->hashPassword($user, 'password'));
        $user->setAdmis(true);
        $user->setEmailValide(true);
        $user->setUnite($entite);
        $this->em->persist($user);

        $plateforme = new Plateforme();
        $plateforme->setPseudoId('Test Plateforme');
        $this->em->persist($plateforme);

        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();

        parent::tearDown();
    }

    public function testConnexionMailInconnu(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test667@example.com:password')]
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'email ou le mot de passe est incorrect.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testConnexionMdpIncorrect(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:password667')]
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'email ou le mot de passe est incorrect.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testConnexionMailNonVerifie(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setEmailValide(false);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:password')]
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.email_non_verifie'),
            $data['message']
        );

        $this->assertVerificationEmailSentTo('test@example.com');
    }

    public function testConnexionNonAdmis(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setAdmis(false);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:password')]
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.compte_non_approuve'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testConnexionSucces(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:password')]
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertStringContainsString(
            'refresh_token',
            (string) $client->getResponse()->headers->get('Set-Cookie')
        );

        self::assertEmailCount(0);
    }

    public function testDeconnexionSansCookie(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/deconnexion',
            [],
            [],
            []
        );

        $this->assertResponseStatusCodeSame(200);

        self::assertEmailCount(0);
    }

    public function testDeconnexionAvecCookie(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $user->getRefreshTokens()->add(
            (new \App\Entity\RefreshToken())
                ->setToken('abcde')
                ->setUtilisateur($user)
                ->setExpiration((new \DateTime())->modify('1 hour'))
        );
        $this->em->flush();

        $client->getCookieJar()->set(
            new Cookie('refresh_token', 'abcde', time() + 3600, '/', "", false, true)
        );

        $client->request(
            'POST',
            '/api/deconnexion',
            [],
            [],
            []
        );

        $this->assertResponseStatusCodeSame(200);

        $this->assertStringContainsString(
            'refresh_token',
            (string) $client->getResponse()->headers->get('Set-Cookie')
        );

        $date = new \DateTime();
        $expirationCookie = $client->getResponse()->headers->getCookies()[0]->getExpiresTime();
        $this->assertLessThan($date->getTimestamp(), $expirationCookie);

        $empty = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->getRefreshTokens()->isEmpty();
        $this->assertTrue($empty);

        self::assertEmailCount(0);
    }

    public function testInscriptionSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'prenom' => 'User',
                'email' => 'testinscription@example.com',
                'mdp' => 'password',
                'directeur_unite_demande' => true
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messageArray', $data);
        $this->assertContains('Le nom est obligatoire', $data['messageArray']);

        self::assertEmailCount(0);
    }

    public function testInscriptionSansPrenom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'email' => 'testinscription@example.com',
                'mdp' => 'password',
                'directeur_unite_demande' => true
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messageArray', $data);
        $this->assertContains('Le prénom est obligatoire', $data['messageArray']);

        self::assertEmailCount(0);
    }

    public function testInscriptionSansMail(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'mdp' => 'password',
                'directeur_unite_demande' => true
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messageArray', $data);
        $this->assertContains('L\'email est obligatoire', $data['messageArray']);

        self::assertEmailCount(0);
    }

    public function testInscriptionSansAffiliation(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'testinscription@example.com',
                'mdp' => 'password'
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messageArray', $data);
        $this->assertContains('Au moins une demande doit être faite (plateforme, unité)', $data['messageArray']);

        self::assertEmailCount(0);
    }

    public function testInscriptionEntiteDemande(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'testinscription@example.com',
                'mdp' => 'password',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'directeur_unite_demande' => true
            ])
        );

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'testinscription@example.com']);
        $this->assertNotNull($user);
        $this->assertFalse($user->isEmailValide());
        $this->assertFalse($user->isAdmis());

        $userInscription = $user->getUtilisateurInscription();
        $this->assertNotNull($userInscription);
        $this->assertTrue($userInscription->isDirecteurUniteDemande());
        $this->assertNull($userInscription->getPlateformeDemande());

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.inscription_reussie'), $data['message']);
        $this->assertNotNull($user->getUtilisateurVerification());
        $this->assertEquals('email_validation', $user->getUtilisateurVerification()->getType());
        $this->assertNotNull($user->getUtilisateurVerification()->getToken());

        $this->assertVerificationEmailSentTo('testinscription@example.com');
    }

    public function testInscriptionPlateformeDemande(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'testinscription@example.com',
                'mdp' => 'password',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'plateforme_demande_id' => $this->em->getRepository(Plateforme::class)->findOneBy([])->getId()
            ])
        );

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'testinscription@example.com']);
        $this->assertNotNull($user);
        $this->assertFalse($user->isEmailValide());
        $this->assertFalse($user->isAdmis());

        $userInscription = $user->getUtilisateurInscription();
        $this->assertNotNull($userInscription);
        $this->assertFalse($userInscription->isDirecteurUniteDemande());
        $this->assertNotNull($userInscription->getPlateformeDemande());

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.inscription_reussie'), $data['message']);
        $this->assertNotNull($user->getUtilisateurVerification());
        $this->assertEquals('email_validation', $user->getUtilisateurVerification()->getType());
        $this->assertNotNull($user->getUtilisateurVerification()->getToken());

        $this->assertVerificationEmailSentTo('testinscription@example.com');
    }

    public function testRefreshTokenNonEnvoye(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/refresh',
            [],
            [],
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('exception.bad_request.refresh_token_manquant'), $data['message']);

        self::assertEmailCount(0);
    }

    public function testMauvaisRefreshToken(): void
    {
        $client = $this->client;

        $client->getCookieJar()->set(
            new Cookie('refresh_token', 'mauvais_token', time() + 3600, '/', "", false, true)
        );

        $client->request(
            'POST',
            '/api/refresh',
            [],
            []
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.exception.refresh_token_invalide_ou_expire'), $data['message']);

        self::assertEmailCount(0);
    }

    public function testRefreshTokenExpire(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $user->getRefreshTokens()->add(
            (new \App\Entity\RefreshToken())
                ->setToken('abcde')
                ->setUtilisateur($user)
                ->setExpiration((new \DateTime())->modify('-1 hour'))
        );
        $this->em->flush();

        $client->getCookieJar()->set(
            new Cookie('refresh_token', 'abcde', time() + 3600, '/', "", false, true)
        );

        $client->request(
            'POST',
            '/api/refresh',
            [],
            []
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.exception.refresh_token_invalide_ou_expire'), $data['message']);

        self::assertEmailCount(0);
    }

    public function testRefreshTokenSuccess(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $user->getRefreshTokens()->add(
            (new \App\Entity\RefreshToken())
                // SHA256 de abcde
                ->setToken('36bbe50ed96841d10443bcb670d6554f0a34b761be67ec9c4a8ad2c0c44ca42c')
                ->setUtilisateur($user)
                ->setExpiration((new \DateTime())->modify('1 hour'))
        );
        $this->em->flush();

        $client->getCookieJar()->set(
            new Cookie('refresh_token', 'abcde', time() + 3600, '/', "", false, true)
        );

        $client->request(
            'POST',
            '/api/refresh',
            [],
            []
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testConnexionFedeUtilisateurInexistant(): void
    {
        $client = $this->client;

        $client->request(
            'GET',
            '/api/connexionFede',
            [],
            [],
            ["CONTENT_TYPE" => "application/json", "HTTP_MAIL" => "utilisateur.inexistant@example.com", "HTTP_GIVENNAME" => "Test", "HTTP_SN" => "User"],
        );

        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/inscription', $client->getResponse()->headers->get('Location'));

        $user = $this->em->getRepository(UtilisateurInscriptionOAuth::class)->findOneBy(['email' => 'utilisateur.inexistant@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Test', $user->getPrenom());
        $this->assertEquals('User', $user->getNom());
        $this->assertNotNull($user->getToken());
    }

    public function testConnexionFedeSuccess(): void
    {
        $client = $this->client;

        $client->request(
            'GET',
            '/api/connexionFede',
            [],
            [],
            ["CONTENT_TYPE" => "application/json", "HTTP_MAIL" => "test@example.com", "HTTP_GIVENNAME" => "Test", "HTTP_SN" => "User"],
        );

        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('/callbackCas', $client->getResponse()->headers->get('Location'));
        $this->assertStringContainsString(
            'refresh_token',
            (string) $client->getResponse()->headers->get('Set-Cookie')
        );
    }

    public function testInscriptionFedeSansToken(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription?ssmdp=true',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'inscriptionOauth@example.com',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'directeur_unite_demande' => true
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messageArray', $data);
        $this->assertEquals('Le token est obligatoire', $data['messageArray'][0]);
    }

    public function testInscriptionFedeInvalide(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/inscription?ssmdp=true',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'inscriptionOauth@example.com',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'directeur_unite_demande' => true,
                'token' => 'token_invalide'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Token d\'inscription OAuth invalide.', $data['message']);
    }

    public function testInscriptionFedeTokenExpire(): void
    {
        $client = $this->client;

        $utilisateurInscriptionOauth = new UtilisateurInscriptionOAuth();
        $utilisateurInscriptionOauth->setEmail('inscriptionOauth@example.com');
        $utilisateurInscriptionOauth->setNom('User');
        $utilisateurInscriptionOauth->setPrenom('Test');
        $utilisateurInscriptionOauth->setToken('token123');
        $utilisateurInscriptionOauth->setExpiration((new \DateTime())->modify('-1 hour'));

        $this->em->persist($utilisateurInscriptionOauth);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/inscription?ssmdp=true',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'inscriptionOauth@example.com',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'directeur_unite_demande' => true,
                'token' => 'token123'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Token d\'inscription OAuth expiré.', $data['message']);
    }

    public function testInscriptionFedeSuccess(): void
    {
        $client = $this->client;

        $utilisateurInscriptionOauth = new UtilisateurInscriptionOAuth();
        $utilisateurInscriptionOauth->setEmail('inscriptionoauth@example.com');
        $utilisateurInscriptionOauth->setNom('User');
        $utilisateurInscriptionOauth->setPrenom('Test');
        $utilisateurInscriptionOauth->setToken('token123');
        $utilisateurInscriptionOauth->setExpiration((new \DateTime())->modify('+1 hour'));

        $this->em->persist($utilisateurInscriptionOauth);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/inscription?ssmdp=true',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode([
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'inscriptionOauth@example.com',
                'unite_id' => $this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true])->getId(),
                'directeur_unite_demande' => true,
                'token' => 'token123'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.inscription_reussie_attente_approbation'), $data['message']);
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'inscriptionoauth@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Test', $user->getPrenom());
        $this->assertEquals('User', $user->getNom());
        $this->assertTrue($user->isEmailValide());
        $this->assertFalse($user->isAdmis());
        $this->assertNull($user->getUtilisateurVerification());
    }

    public function testVerificationMailSansToken(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/verifie-mail',
            [],
            [],
            []
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('utilisateur.exception.token_obligatoire'), $data['message']);
    }

    public function testVerificationMailFauxToken(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/verifie-mail',
            [],
            [],
            [],
            \json_encode(['token' => 'token_invalide'])
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.token_invalide'),
            $data['message']
        );
    }

    public function testVerificationMailTokenExpire(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setEmailValide(false);
        $utilisateurVerif = new UtilisateurVerification();
        $utilisateurVerif->setUtilisateur($user)->setToken("token_expirée")->setExpiration((new \DateTime())->modify('-1 hour'))->setType('email_validation');
        $user->setUtilisateurVerification($utilisateurVerif);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/verifie-mail',
            [],
            [],
            [],
            \json_encode(['token' => 'token_expirée'])
        );

        $this->assertResponseStatusCodeSame(410);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.token_expire'),
            $data['message']
        );
        $this->assertNull($this->em->getRepository(UtilisateurVerification::class)->findOneBy(['token' => 'token_expirée']));
    }

    public function testVerificationMailTokenSuccess(): void
    {
        $client = $this->client;

        // Création de 2 admin
        $admin = new Utilisateur();
        $admin->setEmail('admin@example.com');
        $admin->setPrenom('Admin');
        $admin->setNom('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUnite($this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true]));
        $this->em->persist($admin);

        $admin2 = new Utilisateur();
        $admin2->setEmail('admin2@example.com');
        $admin2->setPrenom('Admin2');
        $admin2->setNom('User2');
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setUnite($this->em->getRepository(Entite::class)->findOneBy(["laboratoire" => true]));
        $this->em->persist($admin2);

        $this->em->flush();

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setEmailValide(false);
        $utilisateurVerif = new UtilisateurVerification();
        $utilisateurVerif->setUtilisateur($user)->setToken("token")->setExpiration((new \DateTime())->modify('+1 hour'))->setType('email_validation');
        $user->setUtilisateurVerification($utilisateurVerif);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/verifie-mail',
            [],
            [],
            [],
            \json_encode(['token' => 'token'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.email_verifie'), $data['message']);
        $this->assertNull($this->em->getRepository(UtilisateurVerification::class)->findOneBy(['token' => 'token']));

        self::assertEmailCount(3);
        $message = self::getMailerMessage(0);
        $this->assertNotNull($message);
        self::assertEmailAddressContains($message, 'to', 'test@example.com');
        $message1 = self::getMailerMessage(1);
        $message2 = self::getMailerMessage(2);
        $this->assertNotNull($message1);
        $this->assertNotNull($message2);
        try {
            self::assertEmailAddressContains($message1, 'to', 'admin@example.com');
            self::assertEmailAddressContains($message2, 'to', 'admin2@example.com');
        } catch (\PHPUnit\Framework\AssertionFailedError) {
            self::assertEmailAddressContains($message1, 'to', 'admin2@example.com');
            self::assertEmailAddressContains($message2, 'to', 'admin@example.com');
        }
    }

    public function testMotDePasseOublieMailInconnu(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['email' => 'inconnu@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.envoie_email_reinitialisation_mdp'), $data['message']);
        self::assertEmailCount(0);
    }

    public function testMotDePasseOublieMailConnu(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['email' => 'test@example.com'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.envoie_email_reinitialisation_mdp'), $data['message']);
        self::assertEmailCount(1);

        self::assertNotNull($this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->getUtilisateurVerification());
    }

    public function testMotDePasseOublieResetSuccess(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setUtilisateurVerification(
            (new UtilisateurVerification())
                ->setToken('token')
                ->setExpiration((new \DateTime())->modify('+1 hour'))
                ->setType('password_reset')
        );

        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['token' => 'token', 'mdp' => 'newpassword'])
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.succes.reinitialisation_mdp_reussie'), $data['message']);
        self::assertEmailCount(0);
    }

    public function testMotDePasseOublieResetSansToken(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setUtilisateurVerification(
            (new UtilisateurVerification())
                ->setToken('token')
                ->setExpiration((new \DateTime())->modify('+1 hour'))
                ->setType('password_reset')
        );

        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['mdp' => 'newpassword'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('utilisateur.exception.token_obligatoire'), $data['message']);
        self::assertEmailCount(0);
    }

    public function testMotDePasseOublieResetSansPassword(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setUtilisateurVerification(
            (new UtilisateurVerification())
                ->setToken('token')
                ->setExpiration((new \DateTime())->modify('+1 hour'))
                ->setType('password_reset')
        );

        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['token' => 'token'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('auth.exception.mdp_obligatoire'), $data['message']);
        self::assertEmailCount(0);
    }

    public function testMotDePasseOublieResetAvecFauxToken(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setUtilisateurVerification(
            (new UtilisateurVerification())
                ->setToken('token')
                ->setExpiration((new \DateTime())->modify('+1 hour'))
                ->setType('password_reset')
        );

        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['token' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'mdp' => 'newpassword'])
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.token_invalide'),
            $data['message']
        );
        self::assertEmailCount(0);
    }

    public function testMotDePasseOublieResetAvecTokenExpire(): void
    {
        $client = $this->client;

        $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com'])->setUtilisateurVerification(
            (new UtilisateurVerification())
                ->setToken('token')
                ->setExpiration((new \DateTime())->modify('-1 hour'))
                ->setType('password_reset')
        );

        $this->em->flush();

        $client->request(
            'PATCH',
            '/api/mdp-oublie',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            \json_encode(['token' => 'token', 'mdp' => 'newpassword'])
        );

        $this->assertResponseStatusCodeSame(410);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('auth.exception.token_expire'),
            $data['message']
        );
        self::assertEmailCount(0);
    }

    public function testLimitConnexion(): void
    {
        $client = $this->client;

        // Clear les tokens de login
        $factory = static::getContainer()->get('limiter.login');
        $factory->create('127.0.0.1')->reset();

        for ($i = 0; $i < 100; $i++) {
            $client->request(
                'POST',
                '/api/connexion',
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:mauvaismotdepasse')]
            );
        }

        $client->request(
            'POST',
            '/api/connexion',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('test@example.com:mauvaismotdepasse')]
        );

        $this->assertResponseStatusCodeSame(429);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(static::getContainer()->get('translator')->trans('exception.rate_limit.tentative_connexion'), $data['message']);
        $factory->create('127.0.0.1')->reset();
        self::assertEmailCount(0);
    }
}
