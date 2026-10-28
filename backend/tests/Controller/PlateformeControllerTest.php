<?php

namespace App\Tests\Controller;

use App\Entity\Bap;
use App\Entity\Categorie;
use App\Entity\ContratMaintenance;
use App\Entity\Corps;
use App\Entity\DeterminationTarifMethode;
use App\Entity\EmploiType;
use App\Entity\Entite;
use App\Entity\ModaliteRecrutement;
use App\Entity\Operation;
use App\Entity\Parametre;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Entity\Ville;
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
        $conn->executeStatement($platform->getTruncateTableSQL('utilisateur', true));
        $conn->executeStatement($platform->getTruncateTableSQL('plateforme', true));
        $conn->executeStatement($platform->getTruncateTableSQL('parametre', true));
        $conn->executeStatement($platform->getTruncateTableSQL('entite', true));
        $conn->executeStatement($platform->getTruncateTableSQL('type_contrat', true));
        $conn->executeStatement($platform->getTruncateTableSQL('corps', true));
        $conn->executeStatement($platform->getTruncateTableSQL('bap', true));
        $conn->executeStatement($platform->getTruncateTableSQL('emploi_type', true));
        $conn->executeStatement($platform->getTruncateTableSQL('operation', true));
        $conn->executeStatement($platform->getTruncateTableSQL('ville', true));
        $conn->executeStatement($platform->getTruncateTableSQL('modalite_recrutement', true));
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

        $labCat = new Categorie();
        $labCat->setNom("Laboratoire");
        $this->em->persist($labCat);

        $unite = new Entite();
        $unite->setEpic(false);
        $unite->setNom("Unité de test");
        $unite->setEmployeur(true);
        $unite->setLaboratoire(false);
        $unite->setCategorie($labCat);
        $unite->setUniversite(false);
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

        $ville = new Ville();
        $ville->setNom("Paris");
        $ville->setNumeroDepartement(75);
        $ville->setDepartement("Paris");
        $this->em->persist($ville);

        $categorie = new Categorie();
        $categorie->setNom("Catégorie 1");
        $this->em->persist($categorie);

        $operation = new Operation();
        $operation->setNom("Opération 1");
        $this->em->persist($operation);

        $entite1 = new Entite();
        $entite1->setEpic(false);
        $entite1->setNom("Entité 1");
        $entite1->setId(1);
        $entite1->setUniversite(false);
        $entite1->setEmployeur(true);
        $entite1->setLaboratoire(true);
        $entite1->setCategorie($categorie);
        $this->em->persist($entite1);

        $entite2 = new Entite();
        $entite2->setEpic(false);
        $entite2->setId(2);
        $entite2->setNom("Entité 2");
        $entite2->setUniversite(false);
        $entite2->setCategorie($categorie);
        $entite2->setEmployeur(true);
        $entite2->setLaboratoire(true);
        $this->em->persist($entite2);

        $bap1 = new Bap();
        $bap1->setNom("BAP 1");
        $bap1->setLettre("A");
        $this->em->persist($bap1);

        $bap2 = new Bap();
        $bap2->setNom("BAP 2");
        $bap2->setLettre("B");
        $this->em->persist($bap2);

        $determinationTarifMethode = new DeterminationTarifMethode();
        $determinationTarifMethode->setNom("Méthode de détermination de tarif 1");
        $this->em->persist($determinationTarifMethode);

        $typeContrat = new TypeContrat();
        $typeContrat->setNom("Type Contrat 1");
        $this->em->persist($typeContrat);

        $contratMaintenance = new ContratMaintenance();
        $contratMaintenance->setNom("Contrat Maintenance 1");
        $this->em->persist($contratMaintenance);

        $emploiType1 = new EmploiType();
        $emploiType1->setNom("Emploi Type 1");
        $emploiType1->setAcronyme("ET1");
        $emploiType1->addBap($bap1);
        $this->em->persist($emploiType1);

        $emploiType2 = new EmploiType();
        $emploiType2->setNom("Emploi Type 2");
        $emploiType2->setAcronyme("ET2");
        $emploiType2->addBap($bap2);
        $this->em->persist($emploiType2);

        $corps = new Corps();
        $corps->setNom("Corps 1");
        $corps->setCompatibleCNU(true);
        $corps->setCompatibleBAP(false);
        $corps->setCompatibleSection(false);
        $this->em->persist($corps);

        $modaliteRecrutement = new ModaliteRecrutement();
        $modaliteRecrutement->setNom("Modalité de recrutement 1");
        $this->em->persist($modaliteRecrutement);

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
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('plateforme.exception.creer_plateforme'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testcreatePlateformeDirOsu(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $user->setRoles(['ROLE_DIR_OSU']);
        $this->em->persist($user);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
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

    public function testcreatePlateformeDirOsuDelegue(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $user->setRoles(['ROLE_DIR_OSU_DELEGUE']);
        $this->em->persist($user);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
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

    public function testcreatePlateformeDU(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $unite = $this->em->getRepository(Entite::class)->findOneBy([]);
        $unite->setDirecteur($user);
        $this->em->persist($unite);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
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

    public function testcreatePlateformeDUD(): void
    {
        $client = $this->client;

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'test@example.com']);
        $unite = $this->em->getRepository(Entite::class)->findOneBy([]);
        $unite->addDelegueDirecteur($user);
        $this->em->persist($unite);
        $this->em->flush();

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
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

    public function testcreatePlateformeSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('plateforme.exception.plateforme_nom_manquant'),
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
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            static::getContainer()->get('translator')->trans('annee_saisie.exception.annee_saisie_nf'),
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testcreatePlateformeSuccess(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
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

    private function getPlateformeCompleteArray()
    {
        $bap = $this->em->createQueryBuilder()
            ->select('b.id')
            ->from(Bap::class, 'b')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        $bapArray = array_column($bap, 'id');

        $corps = $this->em->getRepository(Corps::class)->findOneBy([]);

        $entites = $this->em->createQueryBuilder()
            ->select('e.id')
            ->from(Entite::class, 'e')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        $entiteIds = array_column($entites, 'id');

        $emploiType = $this->em->createQueryBuilder()
            ->select('et.id')
            ->from(EmploiType::class, 'et')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        $emploiTypeArray = array_column($emploiType, 'id');

        $array = [
            "nom_officiel" => "Plateforme Test",
            "forme_developpee_nom_officiel" => "Plateforme de Test",
            "site_web" => "https://www.plateformetest.com",
            "ville_id" => $this->em->getRepository(Ville::class)->findOneBy([])->getId(),
            "logo_url" => "https://www.plateformetest.com/logo.png",
            "autre_type_labellisation" => "Label Test",
            "laboratoire_operateur_id" => $this->em->getRepository(Entite::class)->findOneBy([])->getId(),
            "tutelles_principales_ids" => [$this->em->getRepository(Entite::class)->findOneBy([])->getId()],
            "tutelles_secondaires_ids" => [$this->em->getRepository(Entite::class)->findOneBy([])->getId()],
            "latitude" => 48.8566,
            "longitude" => 2.3522,
            "espace_bureau" => 20.2,
            "espace_laboratoire" => 30.5,
            "personnels" => [
                [
                    "utilisateur_id" => $this->em->getRepository(Utilisateur::class)->findOneBy([])->getId(),
                    "quotite" => 100
                ]
            ],
            "nombre_autres_etudiants" => 5,
            "nombre_apprentis_alternants" => 3,
            "postes_futurs" => [
                [
                    "employeur_id" => $this->em->getRepository(Entite::class)->findOneBy([])->getId(),
                    "corps_id" => $this->em->getRepository(Corps::class)->findOneBy([])->getId(),
                    "modalite_recrutement_id" => $this->em->getRepository(ModaliteRecrutement::class)->findOneBy([])->getId(),
                    "baps_ids" => $bapArray,
                    "emploi_types_ids" => $emploiTypeArray,
                    "quotite" => 50,
                    "rang_numerateur" => 1,
                    "rang_denominateur" => 2
                ]
            ],
            "instruments" => [
                [
                    "id" => "griejgeroi",
                    "futur" => false,
                    "nombre" => 1,
                    "nom" => "Instrument Test",
                    "marque" => "Marque test",
                    "modele" => "Modèle Test",
                    "annee_acquisition" => 2020,
                    "cout" => 10000.2,
                    "valeur_remplacement" => 12000.5,
                    "duree_utilisation" => 5,
                    "source_financement" => [
                        [
                            "financeur_id" => $entiteIds[0],
                            "taux" => 50,
                        ],
                        [
                            "financeur_id" => $entiteIds[1],
                            "taux" => 50,
                        ]
                    ],
                    "contrat_maintenance_id" => $this->em->getRepository(ContratMaintenance::class)->findOneBy([])->getId(),
                    "commentaire_nature_contrat" => "Contrat de test",
                    "fournisseur_contrat" => "Fournisseur Test",
                    "mutualisation_autre_instrument" => false,
                    "mutualisation_site" => false,
                    "mutualisation_umr_osu" => false,
                    "cout_maintenance" => 10000.2,
                ],
                [
                    "id" => "UUUUUUUUUUUUU",
                    "nombre" => 1,
                    "futur" => true,
                    "nom" => "Instrument Test",
                    "marque" => "Marque test",
                    "modele" => "Modèle Test",
                    "operation_id" => $this->em->getRepository(Operation::class)->findOneBy([])->getId(),
                    "annee_achat_envisagee" => 2025,
                    "cout" => 10000.2,
                    "source_financement" => [
                        [
                            "financeur_id" => $entiteIds[0],
                            "taux" => 50,
                        ],
                        [
                            "financeur_id" => $entiteIds[1],
                            "taux" => 50,
                        ]
                    ],
                ]
            ],
            "materiel_consommable" => 10.2,
            "contrat_entretien" => 10.3,
            "etude_prestation" => 10.4,
            "fluide_energie" => 10.5,
            "mission_frais_generaux" => 10.6,
            "determination_tarif_plateforme_id" => $this->em->getRepository(DeterminationTarifMethode::class)->findOneBy([])->getId(),
            "contribution_tutelle_taux" => 25,
            "contribution_tutelle_detail" => [
                [
                    "financeur_id" => $entiteIds[0],
                    "taux" => 50,
                ],
                [
                    "financeur_id" => $entiteIds[1],
                    "taux" => 50,
                ]
            ],
            "contribution_fond_public_taux" => 25,
            "contribution_fond_prive_taux" => 10,
            "contribution_credit_taux" => 20,
            "contribution_projet_recherche_taux" => 4,
            "contribution_structure_pia_taux" => 16,
            "dotation_instrument_national" => 10.2,
            "labellisation_qualite" => \true,
            "nom_labelisation_qualite" => "Label de qualité Test",
            "procedure_transparente_tous" => \true,
            "procedure_transparente_prestation_exterieure" => \true,
            "accueil_international_taux" => 50,
            "modele_77_2424_taux" => 50,
            "modele_77_1224_taux" => 10,
            "modele_57_2424_taux" => 25,
            "modele_57_1224_taux" => 15,
            "utilisation_fonctionnement_taux" => 50,
            "utilisation_maintenance_taux" => 10,
            "utilisation_developpement_taux" => 20,
            "utilisation_panne_taux" => 15,
            "utilisation_temps_mort_taux" => 5,
            "utilisation_equipe_operante_taux" => 50,
            "utilisation_collaboration_interne_taux" => 20,
            "utilisation_collaboration_externe_taux" => 25,
            "utilisation_prestation_service_taux" => 5,
            "bges_realise" => \true,
            "bges_domicile_travail" => 15.2,
            "bges_mission" => 10.2,
            "bges_mission_ext" => 5.2,
            "bges_consommable" => 2.5,
            "bges_equipement" => 3.5,
            "bges_envoi_echantillon" => 1.5,
            "bges_batiment" => 4.5,
            "bges_dechet" => 0.5,
            "publication_equipe_operante" => 10,
            "publication_collaboration_interne" => 5,
            "publication_collaboration_externe" => 3,
            "publication_autre_type" => 5,
            "publication_these" => 2,
        ];

        return $array;
    }

    public function testSauvegarderPlateformeSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["nom_officiel"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom officiel de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom officiel de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansSiteWeb(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["site_web"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le site web de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansVille(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["ville_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La ville de la plateforme est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansLaboratoireOperateur(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["laboratoire_operateur_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le laboratoire opérateur de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTutellePrincipale(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["tutelles_principales_ids"] = [];

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Au moins une tutelle principale de la plateforme est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansLatitude(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["latitude"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La latitude de la plateforme est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansLongitude(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["longitude"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La longitude de la plateforme est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansEspaceBureau(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["espace_bureau"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'espace de bureau de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansEspaceLaboratoire(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["espace_laboratoire"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'espace de laboratoire de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePersonnelSansQuotite(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["personnels"][0]["quotite"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansEmployeur(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["employeur_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'employeur du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'employeur du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansModaliteDeRecrutement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["modalite_recrutement_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La modalité de recrutement du poste futur est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La modalité de recrutement du poste futur est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansCorps(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["corps_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le corps du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le corps du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansBap(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["baps_ids"] = [];

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Au moins une BAP est requise pour le poste futur.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Au moins une BAP est requise pour le poste futur.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansET(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["emploi_types_ids"] = [];

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Au moins un emploi type est requis pour le poste futur.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Au moins un emploi type est requis pour le poste futur.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansQuotite(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["quotite"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La quotité pour le poste futur est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La quotité pour le poste futur est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansRangNumerateur(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["rang_numerateur"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le rang (numérateur) du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le rang (numérateur) du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePosteFuturSansRangDenominateur(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["postes_futurs"][0]["rang_denominateur"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le rang (dénominateur) du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le rang (dénominateur) du poste futur est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansNombreAutresEtudiants(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["nombre_autres_etudiants"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre d'étudiants de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansNombreApprentisAlternants(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["nombre_apprentis_alternants"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre d'apprentis alternants de la plateforme est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["nom"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansNombre(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["nombre"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansAnneeAcquisition(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["annee_acquisition"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'année d'acquisition de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansCout(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["cout"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le coût de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansValeurRemplacement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["valeur_remplacement"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La valeur de remplacement de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansDureeUtilisation(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["duree_utilisation"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La durée d'utilisation de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansSourceFinancement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["source_financement"] = [];

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La source de financement de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansSourceFinancementFinanceurSeulement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["source_financement"][0]["taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de financement de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de financement de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansSourceFinancementTauxSeulement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["source_financement"][0]["financeur_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le financeur de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le financeur de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementActuelSansSourceFinancementPlusde100(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][0]["source_financement"][0]["taux"] = 75;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de source de financement doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansNom(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["nom"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nom de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansNombre(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["nombre"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le nombre de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansAnneeAchatEnvisage(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["annee_achat_envisagee"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "L'année d'achat envisagée de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansCout(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["cout"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Le coût de l'équipement est requis.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansSourceFinancement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["source_financement"] = [];

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "Plateforme sauvegardée avec succès.",
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            "La source de financement de l'équipement est requise.",
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansSourceFinancementFinanceurSeulement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["source_financement"][0]["taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de financement de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de financement de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansSourceFinancementTauxSeulement(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["source_financement"][0]["financeur_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le financeur de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le financeur de l\'équipement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeEquipementFuturSansSourceFinancementPlusde100(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["instruments"][1]["source_financement"][0]["taux"] = 75;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de source de financement doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansMaterielConsommable(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["materiel_consommable"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le coût des matériels et consommables de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansContratEntretien(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contrat_entretien"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le coût des contrats d\'entretien de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansEtudePrestation(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["etude_prestation"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le coût des études et prestations de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansFluideEnergie(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["fluide_energie"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le coût des fluides et énergies de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansMissionFraisGeneraux(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["mission_frais_generaux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le coût des missions et frais généraux de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansDeterminationTarifPlateforme(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["determination_tarif_plateforme_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La méthode de détermination du tarif de la plateforme est requise.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionTutelle(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des tutelle de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionTutelle(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des tutelle doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des tutelle doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionFondPublic(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_fond_public_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond public de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionFondPublic(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_fond_public_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond public de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond public de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionFondPrive(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_fond_prive_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond privé de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionFondPrive(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_fond_prive_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond privé de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du fond privé de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionCredit(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_credit_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du crédit de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionCredit(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_credit_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du crédit de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du crédit de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionProjetRecherche(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_projet_recherche_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du projet de recherche de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionProjetRecherche(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_projet_recherche_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du projet de recherche de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution du projet de recherche de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxContributionStructurePia(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_structure_pia_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des structures PIA de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHors0Et100TauxContributionStructurePia(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_structure_pia_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des structures PIA de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des structures PIA de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxContributionTotalHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_structure_pia_taux"] = 100;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de contribution doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeDetailContributionTutelleManquanteSansContribution(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_taux"] = 0;
        $plateformeData["contribution_tutelle_detail"] = null;
        $plateformeData["contribution_fond_public_taux"] = 50;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeDetailContributionTutelleManquanteAvecContribution(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_detail"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des tutelles est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution des tutelles est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeDetailContributionTutelleTutelleNonPrecise(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_detail"][0]["financeur_id"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La tutelle est requise.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La tutelle est requise.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeDetailContributionTutelleTauxNonPrecise(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_detail"][0]["taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution de la tutelle est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution de la tutelle est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeDetailContributionTutelleTauxHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["contribution_tutelle_detail"][0]["taux"] = 110;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution de la tutelle doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de contribution de la tutelle doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansLabellisationQualiteBool(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["labellisation_qualite"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le statut de labellisation qualité de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansProcedureTransparenteTous(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["procedure_transparente_tous"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le statut de procédure transparente de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansProcedureTransparentePrestaExterieur(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["procedure_transparente_prestation_exterieure"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le statut de procédure transparente pour les prestations extérieures de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeSansTauxAccueilInternational(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["accueil_international_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'accueil international de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeHorsBornesTauxAccueilInternational(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["accueil_international_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'accueil international de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'accueil international de la plateforme doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele772424Null(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_77_2424_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 24 heures sur 24 est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele772424HorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_77_2424_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele771224Null(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_77_1224_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 12 heures sur 24 est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele771224HorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_77_1224_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 7 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele572424Null(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_57_2424_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 24 heures sur 24 est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele572424HorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_57_2424_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 24 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele571224Null(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_57_1224_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 12 heures sur 24 est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModele571224HorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_57_1224_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux de fonctionnement de la plateforme sur le modèle 5 jours sur 7, 12 heures sur 24 doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeModeleTotalHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["modele_57_1224_taux"] = 100;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de modèles d\'utilisation doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationFonctionnementNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_fonctionnement_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en fonctionnement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationFonctionnementHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_fonctionnement_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en fonctionnement doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en fonctionnement doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationMaintenanceNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_maintenance_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en maintenance est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationMaintenanceHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_maintenance_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en maintenance doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en maintenance doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationDevelopementNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_developpement_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en développement est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationDeveloppementHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_developpement_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en développement doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en développement doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationPanneNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_panne_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en panne est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationPanneHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_panne_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en panne doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en panne doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationTempsMortNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_temps_mort_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en temps mort est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationTempsMortHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_temps_mort_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en temps mort doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme en temps mort doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTotalTauxUtilisationHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_temps_mort_taux"] = 100;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de taux d\'utilisation doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationEquipeOperanteNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_equipe_operante_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par l\'équipe opérante est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationEquipeOperanteHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_equipe_operante_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par l\'équipe opérante doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par l\'équipe opérante doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationCollabInterneNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_collaboration_interne_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration interne est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationCollabInterneHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_collaboration_interne_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration interne doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration interne doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationCollabExterneNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_collaboration_externe_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration externe est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationCollabExterneHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_collaboration_externe_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration externe doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la collaboration externe doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationPrestationServiceNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_prestation_service_taux"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la prestation de service est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationPrestationServiceHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_prestation_service_taux"] = 101;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la prestation de service doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le taux d\'utilisation de la plateforme par la prestation de service doit être compris entre 0 et 100.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeTauxUtilisationParQuiTotalHorsBornes(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["utilisation_prestation_service_taux"] = 100;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'La somme des pourcentages de taux d\'utilisation doit être égale à 100%.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesRealiseNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_realise"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le statut de réalisation du bilan carbone global de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesRealiseFalseToutANull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_realise"] = false;
        $plateformeData["bges_domicile_travail"] = null;
        $plateformeData["bges_mission"] = null;
        $plateformeData["bges_mission_ext"] = null;
        $plateformeData["bges_consommable"] = null;
        $plateformeData["bges_equipement"] = null;
        $plateformeData["bges_envoi_echantillon"] = null;
        $plateformeData["bges_batiment"] = null;
        $plateformeData["bges_dechet"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesDomicileTravailNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_domicile_travail"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone domicile-travail de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesDomicileTravailNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_domicile_travail"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone domicile-travail de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone domicile-travail de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesMissionNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_mission"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesMissionNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_mission"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesMissionExtNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_mission_ext"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission externe de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesMissionExtNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_mission_ext"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission externe de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone mission externe de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesConsommableNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_consommable"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone consommable de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesConsommableNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_consommable"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone consommable de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone consommable de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesEquipementNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_equipement"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone équipement de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesEquipementNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_equipement"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone équipement de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone équipement de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesEnvoyeEchantillonNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_envoi_echantillon"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone envoi d\'échantillon de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesEnvoyeEchantillonNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_envoi_echantillon"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone envoi d\'échantillon de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone envoi d\'échantillon de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesBatimentNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_batiment"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone bâtiment de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesBatimentNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_batiment"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone bâtiment de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone bâtiment de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesDechetNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_dechet"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone déchet de la plateforme est requis si le bilan carbone global a été réalisé.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformeBgesDechetNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["bges_dechet"] = -2;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone déchet de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le bilan carbone déchet de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationEquipeOperanteNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_equipe_operante"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de l\'équipe opérante de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationEquipeOperanteNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_equipe_operante"] = -1;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de l\'équipe opérante de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de l\'équipe opérante de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationCollaborationInterneNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_collaboration_interne"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration interne de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationCollaborationInterneNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_collaboration_interne"] = -1;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration interne de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration interne de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationCollaborationExterneNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_collaboration_externe"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration externe de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationCollaborationExterneNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_collaboration_externe"] = -1;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration externe de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de collaboration externe de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationAutreTypeNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_autre_type"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre d\'autre type de publication de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationAutreTypeNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_autre_type"] = -1;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre d\'autre type de publication de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre d\'autre type de publication de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationTheseNull(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_these"] = null;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Plateforme sauvegardée avec succès.',
            $data['message']
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de thèse de la plateforme est requis.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testSauvegarderPlateformePublicationTheseNegatif(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = true;

        $plateformeData["publication_these"] = -1;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de thèse de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            'Le nombre de publications de thèse de la plateforme doit être supérieur ou égal à 0.',
            $data['messageArray'][0]
        );

        self::assertEmailCount(0);
    }

    public function testRecherchePlateforme(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);
        $plateformeData = $this->getPlateformeCompleteArray();
        $plateformeData['nom_officiel'] = 'ADCEF TéST';
        $plateformeData['id'] = $creationResponse['plateforme_id'];

        $plateformeData['brouillon'] = false;

        $client->request(
            'PUT',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode($plateformeData)
        );

        $this->client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
            \json_encode(['nom' => 'ADCEF'])
        );

        self::assertEmailCount(0);
    }

    public function testSuppressionPlateformeForbiddenUser(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);

        $idPlateforme = $data['plateforme_id'];

        self::assertEmailCount(0);

        $client->request(
            'DELETE',
            '/api/plateforme/' . $idPlateforme,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getUserAccessToken()],
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(
            static::getContainer()->get('translator')->trans('plateforme.exception.supprimer_plateforme'),
            $creationResponse['message']
        );

        self::assertEmailCount(0);
    }

    public function testSuppressionPlateformeSuccessAdmin(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/api/plateforme',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
            \json_encode(['nom' => 'Test Plateforme'])
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);

        $idPlateforme = $data['plateforme_id'];

        self::assertEmailCount(0);

        $client->request(
            'DELETE',
            '/api/plateforme/' . $idPlateforme,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()],
        );

        $creationResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(
            'Plateforme supprimée avec succès.',
            $creationResponse['message']
        );

        self::assertEmailCount(0);
    }
}
