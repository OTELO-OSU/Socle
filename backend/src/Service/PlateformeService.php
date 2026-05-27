<?php

namespace App\Service;

use App\DTO\PlateformeHistoriqueDTO;
use App\Entity\Bap;
use App\Entity\ContratMaintenance;
use App\Entity\ContributionTutelle;
use App\Entity\Corps;
use App\Entity\DeterminationTarifMethode;
use App\Entity\EmploiType;
use App\Entity\Entite;
use App\Entity\FinancementInstrument;
use App\Entity\Instrument;
use App\Entity\Operation;
use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\PlateformeHistoriqueTutelle;
use App\Entity\PosteFutur;
use App\Entity\PosteFuturBap;
use App\Entity\PosteFuturEmploiType;
use App\Entity\Publications;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Security\Voter\PlateformeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class PlateformeService
{

    private const PLATEFORME_PAR_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    function getAnneeDeSaisieEnCours(): int
    {
        $anneeSaisieEnCours = $this->em->getRepository(Parametre::class)->findOneBy(["cle" => "annee_saisie_en_cours"]);
        if (!$anneeSaisieEnCours) throw new Exception("Aucune année de saisie n'a été trouvée.", 404);
        return (int) $anneeSaisieEnCours->getValeur();
    }

    function dupliquerPlateformeHistorique($plateformeHisto): PlateformeHistorique
    {
        $anneeSaisieEnCours = $this->getAnneeDeSaisieEnCours();
        $plateformeHisto = $this->em->getRepository(PlateformeHistorique::class)->findOneBy(['annee' => $anneeSaisieEnCours]);
        if (!$plateformeHisto) throw new Exception("Aucun historique de plateforme trouvé pour l'année de saisie en cours.", 404);

        $recordDuplique = clone $plateformeHisto;
        $recordDuplique->setAnnee($anneeSaisieEnCours);
        $recordDuplique->setId(null);
        $this->em->persist($recordDuplique);
        $this->em->flush();

        return $recordDuplique;
    }

    function creerPlateforme(string $nom)
    {
        if (!$this->security->isGranted('CREER', Plateforme::class)) throw new Exception("Vous n'avez pas les droits pour créer une plateforme", 403);

        $plateforme = new Plateforme();
        $plateformeHistorique = new PlateformeHistorique();
        $plateforme->addPlateformeHistorique($plateformeHistorique);
        $plateformeHistorique->setNomOfficiel($nom);
        $plateformeHistorique->setAnnee($this->getAnneeDeSaisieEnCours());
        $plateformeHistorique->setBrouillon(true);

        $this->em->persist($plateforme);
        $this->em->persist($plateformeHistorique);
        $this->em->flush();

        return $plateforme;
    }

    function sauvegarderPlateforme(PlateformeHistoriqueDTO $dto, bool $brouillon)
    {
        /** @var Plateforme $plateforme * */
        $plateforme = $this->em->getRepository(Plateforme::class)->find($dto->id);
        if (!$plateforme) throw new Exception("Plateforme non trouvée.", 404);

        $plateformeHisto = $plateforme->getLastHistorique();
        if (!$plateformeHisto) throw new Exception("Historique de la plateforme non trouvée.", 404);
        if (!$this->security->isGranted('SAUVEGARDER', $plateformeHisto->getPlateforme())) throw new Exception("Vous n'avez pas les droits pour sauvegarder cette plateforme", 403);

        $anneeDto = $plateformeHisto->getAnnee();
        $anneeSaisie = $this->getAnneeDeSaisieEnCours();

        if ($anneeDto > $anneeSaisie) throw new Exception("Impossible de sauvegarder une plateforme pour une année supérieure à l'année de saisie en cours.", 403);

        if ($anneeDto < $anneeSaisie) {
            $dernierRecord = $this->em->getRepository(PlateformeHistorique::class)->findOneBy(['plateforme' => $plateformeHisto->getPlateforme()], ['annee' => 'DESC']);
            if ($dernierRecord->getAnnee() < $anneeSaisie) {
                //* Duplication du record pour l'année de saisie en cours
                $plateformeHisto = $this->dupliquerPlateformeHistorique($plateformeHisto);
            } else {
                throw new Exception("Impossible de sauvegarder une plateforme pour une année inférieure à l'année de saisie en cours.", 403);
            }
        }
        $plateformeHisto = $this->ecraserChampsPlateforme($plateformeHisto, $dto);
        $plateformeHisto->setBrouillon($brouillon);
        $this->em->persist($plateformeHisto);
        $this->em->flush();
    }

    function getPlateformeParPage(int $page, ?string $search): array
    {
        if ($this->security->getUser() === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        $offset = ($page - 1) * self::PLATEFORME_PAR_PAGE;

        $estAdmin = $this->security->isGranted('ROLE_ADMIN');
        $estDirOSU = $this->security->isGranted('ROLE_DIR_OSU');
        $permSuperUser = $estAdmin || $estDirOSU;

        $userId = $this->security->getUser()->getUserIdentifier();

        $conn = $this->em->getConnection();

        // 1) Paginer sur des IDs uniques, triés par nom officiel.
        $idsQb = $conn->createQueryBuilder();
        $idsQb
            ->select('p.id AS plateforme_id', 'ph.nom_officiel AS tri_nom_officiel')
            ->from('plateforme', 'p')
            ->innerJoin('p', 'plateforme_historique', 'ph', 'ph.plateforme_id = p.id')
            ->leftJoin('ph', 'plateforme_historique_personnel', 'pp', 'pp.plateforme_historique_id = ph.id')
            ->leftJoin('pp', 'utilisateur', 'u', 'u.id = pp.utilisateur_id_id')
            ->where('ph.annee = (SELECT MAX(ph2.annee) FROM plateforme_historique ph2 WHERE ph2.plateforme_id = p.id)');

        if ($search) {
            $idsQb->andWhere('(
                ph.nom_officiel ILIKE :search
                OR ph.forme_developpee_nom_officiel ILIKE :search
                OR (
                    (
                        u.prenom ILIKE :search
                        OR u.nom ILIKE :search
                        OR u.email ILIKE :search
                    )
                    AND (
                        pp.responsable_scientifique = true
                        OR pp.responsable_technique = true
                    )
                )
            )');
            $idsQb->setParameter('search', '%' . $search . '%');
        }

        if (!$permSuperUser) {
            $idsQb->andWhere('u.id = :userId')
                ->andWhere('(pp.responsable_scientifique = true OR pp.responsable_technique = true)')
                ->setParameter('userId', $userId);
        }

        $idsQb
            ->groupBy('p.id, ph.nom_officiel')
            ->orderBy('ph.nom_officiel', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::PLATEFORME_PAR_PAGE);

        $idRows = $idsQb->executeQuery()->fetchAllAssociative();
        $plateformeIds = array_map(static fn(array $row): int => (int) $row['plateforme_id'], $idRows);

        $plateformes = [];
        if ($plateformeIds !== []) {
            // 2) Charger les entités pour que les groupes de sérialisation s'appliquent.
            $plateformes = $this->em->getRepository(Plateforme::class)->findBy(['id' => $plateformeIds]);

            // Conserver l'ordre de pagination défini par la requête IDs.
            $ordreParId = array_flip($plateformeIds);
            usort(
                $plateformes,
                static fn(Plateforme $a, Plateforme $b): int => ($ordreParId[$a->getId()] ?? PHP_INT_MAX) <=> ($ordreParId[$b->getId()] ?? PHP_INT_MAX)
            );
        }

        // 3) Total pour la pagination.
        $countQb = $conn->createQueryBuilder();
        $countQb
            ->select('COUNT(DISTINCT p.id)')
            ->from('plateforme', 'p')
            ->innerJoin('p', 'plateforme_historique', 'ph', 'ph.plateforme_id = p.id')
            ->leftJoin('ph', 'plateforme_historique_personnel', 'pp', 'pp.plateforme_historique_id = ph.id')
            ->leftJoin('pp', 'utilisateur', 'u', 'u.id = pp.utilisateur_id_id')
            ->where('ph.annee = (SELECT MAX(ph2.annee) FROM plateforme_historique ph2 WHERE ph2.plateforme_id = p.id)');

        if ($search) {
            $countQb->andWhere('(
                ph.nom_officiel ILIKE :search
                OR ph.forme_developpee_nom_officiel ILIKE :search
                OR (
                    (
                        u.prenom ILIKE :search
                        OR u.nom ILIKE :search
                        OR u.email ILIKE :search
                    )
                    AND (
                        pp.responsable_scientifique = true
                        OR pp.responsable_technique = true
                    )
                )
            )');
            $countQb->setParameter('search', '%' . $search . '%');
        }

        if (!$permSuperUser) {
            $countQb->andWhere('u.id = :userId')
                ->andWhere('(pp.responsable_scientifique = true OR pp.responsable_technique = true)')
                ->setParameter('userId', $userId);
        }

        $total = (int) $countQb->executeQuery()->fetchOne();

        return [
            'plateformes' => $plateformes,
            'page' => $page,
            'pages_total' => ceil($total / self::PLATEFORME_PAR_PAGE),
            'total' => $total
        ];
    }

    function getPlateformeAvecDernierHistorique(int $plateformeId): array
    {
        if ($this->security->getUser() === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("Plateforme non trouvée.", 404);
        }

        if (!$this->security->isGranted(PlateformeVoter::SAUVEGARDER, $plateforme)) {
            throw new Exception("Vous n'avez pas les droits pour consulter cette plateforme", 403);
        }

        $dernierHistorique = $this->em->getRepository(PlateformeHistorique::class)->findOneBy(
            ['plateforme' => $plateforme],
            ['annee' => 'DESC']
        );

        if (!$dernierHistorique) {
            throw new Exception("Aucun historique trouvé pour cette plateforme.", 404);
        }

        return [
            'id' => $plateforme->getId(),
            'pseudo_id' => $plateforme->getPseudoId(),
            'instrument_national' => $plateforme->isInstrumentNational(),
            'dernier_historique' => $dernierHistorique,
        ];
    }

    private function synchroniserInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $value): void
    {
        $payloadParNom = [];
        foreach ($value as $instrument) {
            if ($instrument["nom"] === null) {
                continue;
            }

            $payloadParNom[$instrument["nom"]] = $instrument;
        }

        $this->supprimerInstrumentsActuels($plateformeHisto, $payloadParNom);
        $this->modifierInstrumentsActuels($plateformeHisto, $payloadParNom);
        $this->ajouterInstrumentsActuels($plateformeHisto, $payloadParNom);
    }

    private function supprimerInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $payloadParNom): void
    {
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getNom() === null || !isset($payloadParNom[$instrumentExistant->getNom()])) {
                $plateformeHisto->removeInstrument($instrumentExistant);
                $this->em->remove($instrumentExistant);
            }
        }
    }

    private function modifierInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $payloadParNom): void
    {
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getNom() === null || !isset($payloadParNom[$instrumentExistant->getNom()])) {
                continue;
            }

            $payload = $payloadParNom[$instrumentExistant->getNom()];
            $this->appliquerChampsInstrumentActuel($instrumentExistant, $payload);
            $this->synchroniserMutualisationsInstrumentActuel($instrumentExistant, $payload["mutualise_autre_instrument_ids"] ?? []);
            $this->synchroniserFinancementInstrument($instrumentExistant, $payload["source_financement"] ?? []);
        }
    }

    private function ajouterInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $payloadParNom): void
    {
        $nomsExistants = [];
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getNom() !== null) {
                $nomsExistants[$instrumentExistant->getNom()] = true;
            }
        }

        foreach ($payloadParNom as $nomNormalise => $instrument) {
            if (isset($nomsExistants[$nomNormalise])) {
                continue;
            }

            $newInstrument = new Instrument();
            $newInstrument->setPlateformeHistorique($plateformeHisto);

            $this->appliquerChampsInstrumentActuel($newInstrument, $instrument);

            $plateformeHisto->addInstrument($newInstrument);
            $this->em->persist($newInstrument);

            $this->synchroniserMutualisationsInstrumentActuel($newInstrument, $instrument["mutualise_autre_instrument_ids"] ?? []);
            $this->synchroniserFinancementInstrument($newInstrument, $instrument["source_financement"] ?? []);

            $nomsExistants[$nomNormalise] = true;
        }
    }

    private function appliquerChampsInstrumentActuel(Instrument $instrumentEntity, array $instrument): void
    {
        $instrumentEntity->setNom($instrument["nom"]);
        $instrumentEntity->setMarque($instrument["marque"]);
        $instrumentEntity->setModele($instrument["modele"]);

        if (($instrument["annee_acquisition"] ?? null) !== null) {
            $instrumentEntity->setAnneeAcquisition($instrument["annee_acquisition"]);
        } else {
            $instrumentEntity->setAnneeAcquisition(null);
        }

        $instrumentEntity->setCout($instrument["cout"]);
        $instrumentEntity->setValeurRemplacement($instrument["valeur_remplacement"]);
        $instrumentEntity->setDureeUtilisation($instrument["duree_utilisation"]);
        $instrumentEntity->setFutur(false);

        $contratMaintenanceId = $instrument["type_contrat_id"] ?? $instrument["contrat_maintenance_id"] ?? null;
        $instrumentEntity->setContratMaintenance($contratMaintenanceId !== null ? $this->em->getReference(ContratMaintenance::class, $contratMaintenanceId) : null);
        $instrumentEntity->setCommentaireNatureContrat($instrument["commentaire_nature_contrat"] ?? null);
        $instrumentEntity->setFournisseurContrat($instrument["fournisseur_contrat"] ?? null);
        $instrumentEntity->setMutualiseAutreInstrument($instrument["mutualise_autre_instrument"] ?? false);
        $instrumentEntity->setMutualiseSite($instrument["mutualise_site"] ?? false);
        $instrumentEntity->setMutualiseUmrOsu($instrument["mutualise_umr_osu"] ?? false);
        $instrumentEntity->setCoutMaintenance($instrument["cout_maintenance"] ?? null);
    }

    private function synchroniserMutualisationsInstrumentActuel(Instrument $instrumentEntity, array $mutualisations): void
    {
        foreach ($instrumentEntity->getInstrumentMutualisations() as $record) {
            $instrumentMutualisationId = $record->getInstrumentCible()->getId();
            $detailExiste = false;
            foreach ($mutualisations as $mutualisation) {
                if ($mutualisation == $instrumentMutualisationId) {
                    $detailExiste = true;
                    break;
                }
            }

            if (!$detailExiste) {
                $this->em->remove($record);
            }
        }

        foreach ($mutualisations as $instrumentMutualisationId) {
            $detailExiste = false;
            foreach ($instrumentEntity->getInstrumentMutualisations() as $record) {
                if ($record->getInstrumentCible()->getId() == $instrumentMutualisationId) {
                    $detailExiste = true;
                    break;
                }
            }

            if (!$detailExiste) {
                $newRecord = new \App\Entity\InstrumentMutualisation();
                $newRecord->setInstrumentSource($instrumentEntity);
                $instrumentCible = $this->em->getReference(\App\Entity\Instrument::class, $instrumentMutualisationId);
                $newRecord->setInstrumentCible($instrumentCible);
                $this->em->persist($newRecord);
            }
        }
    }

    private function synchroniserFinancementInstrument(Instrument $instrumentEntity, array $sourcesFinancement): void
    {
        $financementExistant = $instrumentEntity->getFinancementInstruments();
        foreach ($financementExistant as $financement) {
            $financeurId = $financement->getFinanceur()->getId();
            $financementExiste = false;
            foreach ($sourcesFinancement as $financeur) {
                if ($financeur["financeur_id"] == $financeurId) {
                    $financementExiste = true;
                    $financement->setTaux($financeur["taux"]);
                    break;
                }
            }

            if (!$financementExiste) {
                $this->em->remove($financement);
            }
        }

        foreach ($sourcesFinancement as $financeur) {
            $financeurExiste = false;
            foreach ($instrumentEntity->getFinancementInstruments() as $financement) {
                if ($financement->getFinanceur()->getId() == $financeur["financeur_id"]) {
                    $financeurExiste = true;
                    break;
                }
            }

            if (!$financeurExiste) {
                $newFinanceur = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                $newFinancement = new FinancementInstrument();
                $newFinancement->setFinanceur($newFinanceur);
                $newFinancement->setInstrument($instrumentEntity);
                $newFinancement->setTaux($financeur["taux"]);
                $this->em->persist($newFinancement);
            }
        }
    }

    private function synchroniserInstrumentsFuturs(PlateformeHistorique $plateformeHisto, array $value): void
    {
        $instrumentsActuels = $plateformeHisto->getInstruments();
        foreach ($instrumentsActuels as $instrumentExistant) {
            // Ne traiter ici que les instruments futurs.
            if (!$instrumentExistant->isFutur()) {
                continue;
            }

            $instrumentId = $instrumentExistant->getId();
            $existeDeja = false;
            foreach ($value as $instrument) {
                if (!array_key_exists("id", $instrument) || $instrument["id"] === null) continue;
                if ($instrument["id"] == $instrumentId) {
                    $existeDeja = true;
                    // Overrider les champs de l'instrument
                    $instrumentExistant->setNom($instrument["nom"]);
                    $instrumentExistant->setMarque($instrument["marque"]);
                    $instrumentExistant->setModele($instrument["modele"]);
                    $instrumentExistant->setOperation($this->em->getReference(Operation::class, $instrument["operation_id"]));
                    $instrumentExistant->setCout($instrument["cout"]);
                    $instrumentExistant->setAnneeAchatEnvisagee($instrument["annee_achat_envisagee"]);
                    $instrumentExistant->setFutur(true);

                    $financementExistant = $instrumentExistant->getFinancementInstruments();
                    $sourcesFinancement = $instrument["source_financement"] ?? [];
                    foreach ($financementExistant as $financement) {
                        $financeurId = $financement->getFinanceur()->getId();
                        $financementExiste = false;
                        foreach ($sourcesFinancement as $financeur) {
                            if ($financeur["financeur_id"] == $financeurId) {
                                $financementExiste = true;

                                // Overrider les champs du financement
                                $financement->setTaux($financeur["taux"]);
                                break;
                            }
                        }
                        if (!$financementExiste) {
                            $this->em->remove($financement);
                        }
                    }

                    // Ajouter les nouveaux financements
                    foreach ($sourcesFinancement as $financeur) {
                        $financeurExiste = false;
                        foreach ($instrumentExistant->getFinancementInstruments() as $financement) {
                            if ($financement->getFinanceur()->getId() == $financeur["financeur_id"]) {
                                $financeurExiste = true;
                                break;
                            }
                        }
                        if (!$financeurExiste) {
                            $newFinanceur = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                            $newFinancement = new FinancementInstrument();
                            $newFinancement->setFinanceur($newFinanceur);
                            $newFinancement->setInstrument($instrumentExistant);
                            $newFinancement->setTaux($financeur["taux"]);
                            $this->em->persist($newFinancement);
                        }
                    }
                }
            }
            if (!$existeDeja) {
                // L'instrument n'existe plus, il doit être supprimé
                $plateformeHisto->removeInstrument($instrumentExistant);
                $this->em->remove($instrumentExistant);
            }
        }

        // Ajouter les nouveaux instruments
        foreach ($value as $instrument) {
            if (!array_key_exists("id", $instrument) || $instrument["id"] === null) {
                $newInstrument = new \App\Entity\Instrument();
                $newInstrument->setPlateformeHistorique($plateformeHisto);
                $newInstrument->setNom($instrument["nom"]);
                $newInstrument->setMarque($instrument["marque"]);
                $newInstrument->setModele($instrument["modele"]);
                $newInstrument->setOperation($this->em->getReference(Operation::class, $instrument["operation_id"]));
                $newInstrument->setCout($instrument["cout"]);
                $newInstrument->setAnneeAchatEnvisagee($instrument["annee_achat_envisagee"]);
                $newInstrument->setFutur(true);

                $plateformeHisto->addInstrument($newInstrument);
                $this->em->persist($newInstrument);

                foreach (($instrument["source_financement"] ?? []) as $financeur) {
                    $financeurEntite = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                    $newFinancement = new \App\Entity\FinancementInstrument();
                    $newFinancement->setFinanceur($financeurEntite);
                    $newFinancement->setInstrument($newInstrument);
                    $newFinancement->setTaux($financeur["taux"]);
                    $this->em->persist($newFinancement);
                }
            }
        }
    }

    function ecraserChampsPlateforme(PlateformeHistorique $plateformeHisto, PlateformeHistoriqueDTO $dto): PlateformeHistorique
    {
        foreach ($dto as $key => $value) {
            switch ($key) {
                case 'nom_officiel':
                    $plateformeHisto->setNomOfficiel($value);
                    break;
                case 'forme_developpee_nom_officiel':
                    $plateformeHisto->setFormeDeveloppeeNomOfficiel($value);
                    break;
                case 'site_web':
                    $plateformeHisto->setSiteWeb($value);
                    break;
                case 'ville_id':
                    if ($value === null) {
                        $plateformeHisto->setVille(null);
                        break;
                    } else {
                        $plateformeHisto->setVille($this->em->getReference(Ville::class, $value));
                        break;
                    }

                case 'logo_url':
                    $plateformeHisto->setLogoUrl($value);
                    break;
                case 'autre_type_labellisation':
                    $plateformeHisto->setAutreTypeLabellisation($value);
                    break;
                case 'laboratoire_operateur_id':
                    if ($value === null) {
                        $plateformeHisto->setLaboratoireOperateur(null);
                        break;
                    } else {
                        $plateformeHisto->setLaboratoireOperateur($this->em->getReference(Entite::class, $value));
                        break;
                    }
                case 'tutelles_principales_ids':
                    $tutellePrincipaleActuelle = $plateformeHisto->getPlateformeHistoriqueTutelles();
                    foreach ($tutellePrincipaleActuelle as $record) {
                        if ($record->isPrincipale() && !in_array($record->getTutelle()->getId(), $value)) {
                            // La tutelle principale actuelle n'est plus dans la liste des tutelles principales, elle doit être supprimée
                            $plateformeHisto->removePlateformeHistoriqueTutelle($record);
                            $this->em->remove($record);
                        }
                    }
                    foreach ($value as $tutelleId) {
                        $existeDeja = false;
                        foreach ($tutellePrincipaleActuelle as $record) {
                            if ($record->isPrincipale() && $record->getTutelle()->getId() === $tutelleId) {
                                // La tutelle principale existe déjà, pas besoin de la recréer
                                $existeDeja = true;
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            // La tutelle principale n'existe pas encore, elle doit être créée
                            $newRecord = new PlateformeHistoriqueTutelle();
                            $newRecord->setPlateformeHistorique($plateformeHisto);
                            $tutellePrincipale = $this->em->getReference(Entite::class, $tutelleId);
                            $newRecord->setTutelle($tutellePrincipale);
                            $newRecord->setPrincipale(true);
                            $plateformeHisto->addPlateformeHistoriqueTutelle($newRecord);
                            $this->em->persist($newRecord);
                        }
                    }
                    break;
                case 'tutelles_secondaires_ids':
                    $tutelleSecondaireActuelle = $plateformeHisto->getPlateformeHistoriqueTutelles();
                    foreach ($tutelleSecondaireActuelle as $record) {
                        if (!$record->isPrincipale() && !in_array($record->getTutelle()->getId(), $value)) {
                            // La tutelle secondaire actuelle n'est plus dans la liste des tutelles secondaires, elle doit être supprimée
                            $plateformeHisto->removePlateformeHistoriqueTutelle($record);
                            $this->em->remove($record);
                        }
                    }
                    foreach ($value as $tutelleId) {
                        $existeDeja = false;
                        foreach ($tutelleSecondaireActuelle as $record) {
                            if (!$record->isPrincipale() && $record->getTutelle()->getId() === $tutelleId) {
                                // La tutelle secondaire existe déjà, pas besoin de la recréer
                                $existeDeja = true;
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            // La tutelle secondaire n'existe pas encore, elle doit être créée
                            $newRecord = new PlateformeHistoriqueTutelle();
                            $newRecord->setPlateformeHistorique($plateformeHisto);
                            $tutellePrincipale = $this->em->getReference(Entite::class, $tutelleId);
                            $newRecord->setTutelle($tutellePrincipale);
                            $newRecord->setPrincipale(false);
                            $plateformeHisto->addPlateformeHistoriqueTutelle($newRecord);
                            $this->em->persist($newRecord);
                        }
                    }
                    break;
                case 'latitude':
                    $plateformeHisto->setLatitude($value);
                    break;
                case 'longitude':
                    $plateformeHisto->setLongitude($value);
                    break;
                case 'espace_bureau':
                    $plateformeHisto->setEspaceBureau($value);
                    break;
                case 'espace_laboratoire':
                    $plateformeHisto->setEspaceLaboratoire($value);
                    break;
                case 'personnels':
                    // Supprimer parmis les actuelles ceux qui n'existe plus
                    foreach ($plateformeHisto->getPlateformeHistoriquePersonnels() as $personnelExistant) {
                        $personnelId = $personnelExistant->getId();
                        foreach ($value as $perso) {
                            if ($perso["utilisateur_id"] == $personnelId) {
                                $this->em->remove($personnelExistant);
                                break;
                            }
                        }
                    }

                    // Ajouter les nouveaux
                    foreach ($value as $perso) {
                        $nouveauItilisateurId = $perso["utilisateur_id"];
                        $existeDeja = false;
                        foreach ($plateformeHisto->getPlateformeHistoriquePersonnels() as $personnelExistant) {
                            if ($personnelExistant->getUtilisateur()->getId() == $nouveauItilisateurId) {
                                $existeDeja = true;
                                // Overrider la quotité
                                $personnelExistant->setQuotite($perso["quotite"]);
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            $newRecord = new PlateformeHistoriquePersonnel();
                            $newRecord->setPlateformeHistorique($plateformeHisto);
                            $utilisateur = $this->em->getReference(Utilisateur::class, $nouveauItilisateurId);
                            $newRecord->setUtilisateur($utilisateur);
                            $newRecord->setResponsableScientifique($perso["responsable_scientifique"]);
                            $newRecord->setResponsableTechnique($perso["responsable_technique"]);
                            $newRecord->setQuotite($perso["quotite"]);
                            $plateformeHisto->addPlateformeHistoriquePersonnel($newRecord);
                            $this->em->persist($newRecord);
                        }
                    }
                    break;
                case 'postes_futurs':
                    if (!is_array($value)) {
                        break;
                    }

                    $postesParRang = [];
                    foreach ($value as $poste) {
                        if (!isset($poste["rang_numerateur"], $poste["rang_denominateur"])) {
                            continue;
                        }

                        $cleRang = $poste["rang_numerateur"] . '/' . $poste["rang_denominateur"];
                        $postesParRang[$cleRang] = $poste;
                    }

                    foreach ($plateformeHisto->getPosteFuturs() as $posteFuturExistant) {
                        $cleRang = $posteFuturExistant->getRangNumerateur() . '/' . $posteFuturExistant->getRangDenominateur();
                        if (!array_key_exists($cleRang, $postesParRang)) {
                            $plateformeHisto->removePosteFutur($posteFuturExistant);
                            $this->em->remove($posteFuturExistant);
                            continue;
                        }

                        $poste = $postesParRang[$cleRang];

                        // Overrider les champs du poste futur
                        $posteFuturExistant->setCorps($this->em->getReference(Corps::class, $poste["corps_id"]));
                        $posteFuturExistant->setEmployeur($this->em->getReference(Entite::class, $poste["employeur_id"]));
                        $posteFuturExistant->setQuotite($poste["quotite"]);

                        // Supprimer les baps en trop
                        $nouvellesBaps = $poste["baps_ids"] ?? [];
                        foreach ($posteFuturExistant->getBaps() as $bapExistant) {
                            $bapId = $bapExistant->getId();
                            $bapExiste = false;
                            foreach ($nouvellesBaps as $bap) {
                                if ($bap == $bapId) {
                                    $bapExiste = true;
                                    break;
                                }
                            }
                            if (!$bapExiste) {
                                $posteFuturExistant->removeBap($bapExistant);
                            }
                        }

                        // Ajouter les nouveaux baps
                        foreach ($nouvellesBaps as $bap) {
                            $bapExiste = false;
                            foreach ($posteFuturExistant->getBaps() as $bapExistant) {
                                if ($bapExistant->getId() == $bap) {
                                    $bapExiste = true;
                                    break;
                                }
                            }
                            if (!$bapExiste) {
                                $newBap = $this->em->getReference(Bap::class, $bap);
                                $posteFuturExistant->addBap($newBap);
                            }
                        }

                        // Supprimer les emplois type en trop
                        $nouveauxEt = $poste["emploi_types_ids"] ?? [];
                        foreach ($posteFuturExistant->getEmploiTypes() as $etExistant) {
                            $etId = $etExistant->getId();
                            $etExiste = false;
                            foreach ($nouveauxEt as $et) {
                                if ($et == $etId) {
                                    $etExiste = true;
                                    break;
                                }
                            }
                            if (!$etExiste) {
                                $posteFuturExistant->removeEmploiType($etExistant);
                            }
                        }

                        // Ajouter les nouveaux emplois types
                        foreach ($nouveauxEt as $et) {
                            $etExiste = false;
                            foreach ($posteFuturExistant->getEmploiTypes() as $etExistant) {
                                if ($etExistant->getId() == $et) {
                                    $etExiste = true;
                                    break;
                                }
                            }
                            if (!$etExiste) {
                                $newEt = $this->em->getReference(EmploiType::class, $et);
                                $posteFuturExistant->addEmploiType($newEt);
                            }
                        }

                        $this->em->persist($posteFuturExistant);
                        unset($postesParRang[$cleRang]);
                    }

                    // Ajouter les postes futurs manquants, identifiés par le rang.
                    foreach ($postesParRang as $poste) {
                        $newPosteFutur = new PosteFutur();
                        $newPosteFutur->setRangNumerateur($poste["rang_numerateur"]);
                        $newPosteFutur->setRangDenominateur($poste["rang_denominateur"]);
                        $newPosteFutur->setCorps($this->em->getReference(Corps::class, $poste["corps_id"]));
                        $newPosteFutur->setEmployeur($this->em->getReference(Entite::class, $poste["employeur_id"]));
                        $newPosteFutur->setQuotite($poste["quotite"]);

                        foreach (($poste["baps_ids"] ?? []) as $bap) {
                            $newBap = $this->em->getReference(Bap::class, $bap);
                            $newPosteFutur->addBap($newBap);
                        }

                        foreach (($poste["emploi_types_ids"] ?? []) as $et) {
                            $newEt = $this->em->getReference(EmploiType::class, $et);
                            $newPosteFutur->addEmploiType($newEt);
                        }

                        $plateformeHisto->addPosteFutur($newPosteFutur);
                        $this->em->persist($newPosteFutur);
                    }
                    break;
                case "instruments":
                    if (!is_array($value)) {
                        break;
                    }

                    $instrumentsActuels = array_values(array_filter(
                        $value,
                        static fn(array $instrument): bool => ($instrument["futur"] ?? false) !== true
                    ));

                    $instrumentsFuturs = array_values(array_filter(
                        $value,
                        static fn(array $instrument): bool => ($instrument["futur"] ?? false) === true
                    ));

                    $this->synchroniserInstrumentsActuels($plateformeHisto, $instrumentsActuels);
                    $this->synchroniserInstrumentsFuturs($plateformeHisto, $instrumentsFuturs);
                    break;
                case "materiel_consommable":
                    $plateformeHisto->setMaterielConsommable($value);
                    break;
                case "contrat_entretien":
                    $plateformeHisto->setContratEntretien($value);
                    break;
                case "etude_prestation":
                    $plateformeHisto->setEtudePrestation($value);
                    break;
                case "fluide_energie":
                    $plateformeHisto->setFluideEnergie($value);
                    break;
                case "mission_frais_generaux":
                    $plateformeHisto->setMissionFraisGeneraux($value);
                    break;
                case "determination_tarif_plateforme_id":
                    $plateformeHisto->setDeterminationTarifPlateforme($value !== null ? $this->em->getReference(DeterminationTarifMethode::class, $value) : null);
                    break;
                case "contribution_tutelle_taux":
                    $plateformeHisto->setContributionTutelleTaux($value);
                    break;
                case "contribution_tutelle_detail":
                    $detailActuel = $plateformeHisto->getContributionTutelles();
                    foreach ($detailActuel as $record) {
                        $entiteId = $record->getFinanceur()->getId();
                        $existeDeja = false;
                        foreach ($value as $detail) {
                            if ($detail["financeur_id"] == $entiteId) {
                                $existeDeja = true;
                                // Overrider les champs du detail
                                $record->setTaux($detail["taux"]);
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            // Le detail n'existe plus, il doit être supprimé
                            $plateformeHisto->removeContributionTutelle($record);
                            $this->em->remove($record);
                        }
                    }
                    if (is_array($value)) {
                        foreach ($value as $detail) {
                            $entiteId = $detail["financeur_id"];
                            $existeDeja = false;
                            foreach ($detailActuel as $record) {
                                if ($record->getFinanceur()->getId() == $entiteId) {
                                    // Le detail existe déjà, pas besoin de le recréer
                                    $existeDeja = true;
                                    break;
                                }
                            }
                            if (!$existeDeja) {
                                // Le detail n'existe pas encore, il doit être créé
                                $newRecord = new ContributionTutelle();
                                $newRecord->setPlateformeHistorique($plateformeHisto);
                                $financeur = $this->em->getReference(Entite::class, $entiteId);
                                $newRecord->setFinanceur($financeur);
                                $newRecord->setTaux($detail["taux"]);
                                $plateformeHisto->addContributionTutelle($newRecord);
                                $this->em->persist($newRecord);
                            }
                        }
                    }
                    break;
                case "contribution_fond_public_taux":
                    $plateformeHisto->setContributionFondPublicTaux($value);
                    break;
                case "contribution_fond_prive_taux":
                    $plateformeHisto->setContributionFondPriveTaux($value);
                    break;
                case "contribution_credit_taux":
                    $plateformeHisto->setContributionCreditTaux($value);
                    break;
                case "contribution_projet_recherche_taux":
                    $plateformeHisto->setContributionProjetRechercheTaux($value);
                    break;
                case "contribution_structure_pia_taux":
                    $plateformeHisto->setContributionStructurePiaTaux($value);
                    break;
                case "dotation_instrument_national":
                    $plateformeHisto->setDotationInstrumentNational($value);
                    break;
                case "labellisation_qualite":
                    $plateformeHisto->setLabellisationQualite($value);
                    break;
                case "nom_labelisation_qualite":
                    $plateformeHisto->setNomLabellisationQualite($value);
                    break;
                case "procedure_transparente_tous":
                    $plateformeHisto->setProcedureTransparenteTous($value);
                    break;
                case "procedure_transparente_prestation_exterieure":
                    $plateformeHisto->setProcedureTransparentePrestationExterieure($value);
                    break;
                case "accueil_international_taux":
                    $plateformeHisto->setAccueilInternationalTaux($value);
                    break;
                case "modele_77_2424_taux":
                    $plateformeHisto->setModele772424Taux($value);
                    break;
                case "modele_77_1224_taux":
                    $plateformeHisto->setModele771224Taux($value);
                    break;
                case "modele_57_2424_taux":
                    $plateformeHisto->setModele572424Taux($value);
                    break;
                case "modele_57_1224_taux":
                    $plateformeHisto->setModele571224Taux($value);
                    break;
                case "utilisation_fonctionnement_taux":
                    $plateformeHisto->setUtilisationFonctionnementTaux($value);
                    break;
                case "utilisation_maintenance_taux":
                    $plateformeHisto->setUtilisationMaintenanceTaux($value);
                    break;
                case "utilisation_developemment_taux":
                    $plateformeHisto->setUtilisationDeveloppementTaux($value);
                    break;
                case "utilisation_panne_taux":
                    $plateformeHisto->setUtilisationPanneTaux($value);
                    break;
                case "utilisation_temps_mort_taux":
                    $plateformeHisto->setUtilisationTempsMortTaux($value);
                    break;
                case "utilisation_equipe_operante_taux":
                    $plateformeHisto->setUtilisationEquipeOperanteTaux($value);
                    break;
                case "utilisation_collaboration_interne_taux":
                    $plateformeHisto->setUtilisationCollaborationInterneTaux($value);
                    break;
                case "utilisation_collaboration_externe_taux":
                    $plateformeHisto->setUtilisationCollaborationExterneTaux($value);
                    break;
                case "utilisation_prestation_service_taux":
                    $plateformeHisto->setUtilisationPrestationServiceTaux($value);
                    break;
                case "bges_realise":
                    $plateformeHisto->setBgesRealise($value);
                    break;
                case "bges_domicile_travail":
                    $plateformeHisto->setBgesDomicileTravail($value);
                    break;
                case "bges_mission":
                    $plateformeHisto->setBgesMission($value);
                    break;
                case "bges_mission_ext":
                    $plateformeHisto->setBgesMissionExt($value);
                    break;
                case "bges_consommable":
                    $plateformeHisto->setBgesConsommable($value);
                    break;
                case "bges_equipement":
                    $plateformeHisto->setBgesEquipement($value);
                    break;
                case "bges_envoi_echantillon":
                    $plateformeHisto->setBgesEnvoiEchantillon($value);
                    break;
                case "bges_batiment":
                    $plateformeHisto->setBgesBatiment($value);
                    break;
                case "bges_dechet":
                    $plateformeHisto->setBgesDechet($value);
                    break;
                case "publication_equipe_operante":
                    $plateformeHisto->setPublicationEquipeOperante($value);
                    break;
                case "publication_collaboration_interne":
                    $plateformeHisto->setPublicationCollaborationInterne($value);
                    break;
                case "publication_collaboration_externe":
                    $plateformeHisto->setPublicationCollaborationExterne($value);
                    break;
                case "publication_autre_type":
                    $plateformeHisto->setPublicationAutreType($value);
                    break;
                case "publication_these":
                    $plateformeHisto->setPublicationThese($value);
                    break;
                case "nom_labellisation_qualite":
                    $plateformeHisto->setNomLabellisationQualite($value);
                    break;
                case "publications":
                    $publiTraitees = [];
                    foreach ($plateformeHisto->getPublications() as $publicationExistant) {
                        $publicationId = $publicationExistant->getNumero();
                        $existeDeja = false;
                        foreach ($value as $publication) {
                            if ($publication["numero"] == $publicationId) {
                                $existeDeja = true;
                                $publicationExistant->setDoi($publication["doi"]);
                                $publiTraitees[] = $publication["numero"];
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            // La publication n'existe plus, elle doit être supprimée
                            $plateformeHisto->removePublication($publicationExistant);
                            $this->em->remove($publicationExistant);
                        }
                    }

                    foreach ($value as $publication) {
                        $publicationId = $publication["numero"];
                        if (in_array($publicationId, $publiTraitees)) {
                            continue;
                        }

                        // La publication n'existe pas encore, elle doit être créée
                        $newPublication = new Publications();
                        $newPublication->setPlateformeHistorique($plateformeHisto);
                        $newPublication->setNumero($publicationId);
                        $newPublication->setDoi($publication["doi"]);
                        $plateformeHisto->addPublication($newPublication);
                        $this->em->persist($newPublication);
                    }
                    break;
            }
        }

        return $plateformeHisto;
    }
}
