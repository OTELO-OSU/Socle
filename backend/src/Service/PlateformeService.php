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
use App\Entity\ModaliteRecrutement;
use App\Entity\Operation;
use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\PlateformeHistoriqueTutelle;
use App\Entity\PlateformeTags;
use App\Entity\PosteFutur;
use App\Entity\PosteFuturBap;
use App\Entity\PosteFuturEmploiType;
use App\Entity\Publications;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Security\Voter\PlateformeVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class PlateformeService
{

    private const PLATEFORME_PAR_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TagService $tagService,
        private readonly Security $security
    ) {}

    function getAnneeDeSaisieEnCours(): int
    {
        $anneeSaisieEnCours = $this->em->getRepository(Parametre::class)->findOneBy(["cle" => "annee_saisie_en_cours"]);
        if (!$anneeSaisieEnCours) throw new Exception("Aucune année de saisie n'a été trouvée.", 404);
        return (int) $anneeSaisieEnCours->getValeur();
    }

    function dupliquerPlateformeHistorique(PlateformeHistorique $plateformeHisto): PlateformeHistorique
    {
        $anneeSaisieEnCours = $this->getAnneeDeSaisieEnCours();

        $recordDuplique = clone $plateformeHisto;
        $recordDuplique->setId(null);
        $recordDuplique->setAnnee($anneeSaisieEnCours);

        $this->reinitialiserCollectionsPlateformeHistorique($recordDuplique);
        $this->dupliquerRelationsPlateformeHistorique($plateformeHisto, $recordDuplique);

        $this->em->persist($recordDuplique);
        $this->em->flush();

        return $recordDuplique;
    }

    private function reinitialiserCollectionsPlateformeHistorique(PlateformeHistorique $plateformeHistorique): void
    {
        $propertyNames = [
            'plateformeHistoriqueTutelles',
            'plateformeHistoriquePersonnels',
            'posteFuturs',
            'contributionTutelles',
            'instruments',
            'publications',
        ];

        foreach ($propertyNames as $propertyName) {
            $property = new \ReflectionProperty(PlateformeHistorique::class, $propertyName);
            $property->setAccessible(true);
            $property->setValue($plateformeHistorique, new ArrayCollection());
        }
    }

    private function dupliquerRelationsPlateformeHistorique(PlateformeHistorique $source, PlateformeHistorique $target): void
    {
        foreach ($source->getPlateformeHistoriqueTutelles() as $plateformeHistoriqueTutelle) {
            $newRecord = new PlateformeHistoriqueTutelle();
            $newRecord->setPlateformeHistorique($target);
            $newRecord->setTutelle($plateformeHistoriqueTutelle->getTutelle());
            $newRecord->setPrincipale($plateformeHistoriqueTutelle->isPrincipale());
            $target->addPlateformeHistoriqueTutelle($newRecord);
            $this->em->persist($newRecord);
        }

        foreach ($source->getPlateformeHistoriquePersonnels() as $plateformeHistoriquePersonnel) {
            $newRecord = new PlateformeHistoriquePersonnel();
            $newRecord->setPlateformeHistorique($target);
            $newRecord->setUtilisateur($plateformeHistoriquePersonnel->getUtilisateur());
            $newRecord->setResponsableScientifique((bool) $plateformeHistoriquePersonnel->isResponsableScientifique());
            $newRecord->setResponsableTechnique((bool) $plateformeHistoriquePersonnel->isResponsableTechnique());
            $newRecord->setResponsablePlateforme((bool) $plateformeHistoriquePersonnel->isResponsablePlateforme());
            $newRecord->setQuotite((int) $plateformeHistoriquePersonnel->getQuotite());
            $target->addPlateformeHistoriquePersonnel($newRecord);
            $this->em->persist($newRecord);
        }

        foreach ($source->getContributionTutelles() as $contributionTutelle) {
            $newRecord = new ContributionTutelle();
            $newRecord->setPlateformeHistorique($target);
            $newRecord->setFinanceur($contributionTutelle->getFinanceur());
            $newRecord->setTaux((int) $contributionTutelle->getTaux());
            $target->addContributionTutelle($newRecord);
            $this->em->persist($newRecord);
        }

        $instrumentsMap = [];
        foreach ($source->getInstruments() as $instrument) {
            $newInstrument = $this->dupliquerInstrument($instrument, $target);
            $target->addInstrument($newInstrument);
            $this->em->persist($newInstrument);

            if ($instrument->getId() !== null) {
                $instrumentsMap[$instrument->getId()] = $newInstrument;
            }
        }

        foreach ($source->getInstruments() as $instrument) {
            $newInstrument = $instrument->getId() !== null ? ($instrumentsMap[$instrument->getId()] ?? null) : null;
            if ($newInstrument === null) {
                continue;
            }

            foreach ($instrument->getInstrumentMutualisations() as $mutualisation) {
                $newMutualisation = new \App\Entity\InstrumentMutualisation();

                $instrumentCible = $mutualisation->getInstrumentCible();
                if ($instrumentCible !== null && $instrumentCible->getId() !== null && isset($instrumentsMap[$instrumentCible->getId()])) {
                    $newMutualisation->setInstrumentCible($instrumentsMap[$instrumentCible->getId()]);
                } else {
                    $newMutualisation->setInstrumentCible($instrumentCible);
                }

                $newInstrument->addInstrumentMutualisation($newMutualisation);
                $this->em->persist($newMutualisation);
            }

            $financeursTraites = [];
            foreach ($instrument->getFinancementInstruments() as $financementInstrument) {
                $financeurId = $financementInstrument->getFinanceur()?->getId();
                if ($financeurId !== null) {
                    if (isset($financeursTraites[$financeurId])) {
                        continue;
                    }
                    $financeursTraites[$financeurId] = true;
                }

                $newFinancement = new \App\Entity\FinancementInstrument();
                $newFinancement->setFinanceur($financementInstrument->getFinanceur());
                $newFinancement->setTaux((int) $financementInstrument->getTaux());
                $newInstrument->addFinancementInstrument($newFinancement);
                $this->em->persist($newFinancement);
            }
        }

        foreach ($source->getPublications() as $publication) {
            $newRecord = new Publications();
            $newRecord->setPlateformeHistorique($target);
            $newRecord->setNumero($publication->getNumero());
            $newRecord->setDoi($publication->getDoi());
            $target->addPublication($newRecord);
            $this->em->persist($newRecord);
        }
    }

    private function dupliquerInstrument(Instrument $instrument, PlateformeHistorique $plateformeHistorique): Instrument
    {
        $newInstrument = new Instrument();
        $newInstrument->setPlateformeHistorique($plateformeHistorique);
        $newInstrument->setNom($instrument->getNom());
        $newInstrument->setMarque($instrument->getMarque());
        $newInstrument->setModele($instrument->getModele());
        $newInstrument->setAnneeAcquisition($instrument->getAnneeAcquisition());
        $newInstrument->setCout($instrument->getCout());
        $newInstrument->setValeurRemplacement($instrument->getValeurRemplacement());
        $newInstrument->setDureeUtilisation($instrument->getDureeUtilisation());
        $newInstrument->setOperation($instrument->getOperation());
        $newInstrument->setAnneeAchatEnvisagee($instrument->getAnneeAchatEnvisagee());
        $newInstrument->setFutur((bool) $instrument->isFutur());
        $newInstrument->setContratMaintenance($instrument->getContratMaintenance());
        $newInstrument->setCommentaireNatureContrat($instrument->getCommentaireNatureContrat());
        $newInstrument->setFournisseurContrat($instrument->getFournisseurContrat());
        $newInstrument->setMutualiseAutreInstrument((bool) $instrument->isMutualiseAutreInstrument());
        $newInstrument->setMutualiseSite((bool) $instrument->isMutualiseSite());
        $newInstrument->setMutualiseUmrOsu((bool) $instrument->isMutualiseUmrOsu());
        $newInstrument->setCoutMaintenance($instrument->getCoutMaintenance());

        return $newInstrument;
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

    function getPlateformePourcentage(PlateformeHistorique $plateformeHisto): float
    {
        $moyenne = [];

        $moyenne[] = \is_null($plateformeHisto->getNomOfficiel()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getSiteWeb()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getLaboratoireOperateur()) ? 0 : 1;
        $moyenne[] = empty($plateformeHisto->getTutellesPrincipalesIds()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getLatitude()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getLongitude()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getEspaceBureau()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getEspaceLaboratoire()) ? 0 : 1;
        $moyenne[] = empty($plateformeHisto->getPlateformeHistoriquePersonnels()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getNombreAutresEtudiants()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getNombreApprentisAlternants()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getMaterielConsommable()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContratEntretien()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getEtudePrestation()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getFluideEnergie()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getMissionFraisGeneraux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getDeterminationTarifPlateforme()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionTutelleTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionFondPublicTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionFondPriveTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionCreditTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionProjetRechercheTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getContributionStructurePiaTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->isLabellisationQualite()) ? 0 : 1;
        if ($plateformeHisto->isLabellisationQualite()) {
            $moyenne[] = \is_null($plateformeHisto->getNomLabellisationQualite()) ? 0 : 1;
        }
        $moyenne[] = \is_null($plateformeHisto->isProcedureTransparentePrestationExterieure()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->isProcedureTransparenteTous()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getModele772424Taux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getModele771224Taux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getModele572424Taux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getModele571224Taux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationFonctionnementTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationMaintenanceTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationDeveloppementTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationPanneTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationTempsMortTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationEquipeOperanteTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationCollaborationInterneTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationCollaborationExterneTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getUtilisationPrestationServiceTaux()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->isBgesRealise()) ? 0 : 1;
        if ($plateformeHisto->isBgesRealise()) {
            $moyenne[] = \is_null($plateformeHisto->getBgesDomicileTravail()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesMission()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesMissionExt()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesConsommable()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesEquipement()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesEnvoiEchantillon()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesBatiment()) ? 0 : 1;
            $moyenne[] = \is_null($plateformeHisto->getBgesDechet()) ? 0 : 1;
        }

        $moyenne[] = \is_null($plateformeHisto->getPublicationEquipeOperante()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getPublicationCollaborationInterne()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getPublicationCollaborationExterne()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getPublicationAutreType()) ? 0 : 1;
        $moyenne[] = \is_null($plateformeHisto->getPublicationThese()) ? 0 : 1;
        $moyenne[] = empty($plateformeHisto->getPublications()) ? 0 : 1;



        $totalChamps = count($moyenne);
        $champsRemplis = array_sum($moyenne);
        $pourcentage = $totalChamps > 0 ? ($champsRemplis / $totalChamps) * 100 : 0;

        // Fixer à 2 décimal
        $pourcentage = round($pourcentage, 2);

        return $pourcentage;
    }

    function getPlateformePublique(): array
    {
        $conn = $this->em->getConnection();

        // 1) Récupérer les IDs candidats selon la recherche (sans filtrage d'autorisation).
        $idsQb = $conn->createQueryBuilder();
        $idsQb
            ->select('p.id AS plateforme_id', 'ph.nom_officiel AS tri_nom_officiel')
            ->from('plateforme', 'p')
            ->innerJoin('p', 'plateforme_historique', 'ph', 'ph.plateforme_id = p.id')
            ->leftJoin('ph', 'plateforme_historique_personnel', 'pp', 'pp.plateforme_historique_id = ph.id')
            ->leftJoin('pp', 'utilisateur', 'u', 'u.id = pp.utilisateur_id')
            ->where('ph.annee = (SELECT MAX(ph2.annee) FROM plateforme_historique ph2 WHERE ph2.plateforme_id = p.id)');

        $idsQb
            ->groupBy('p.id, ph.nom_officiel')
            ->orderBy('ph.nom_officiel', 'ASC');

        $idRows = $idsQb->executeQuery()->fetchAllAssociative();
        $plateformeIds = array_map(static fn(array $row): int => (int) $row['plateforme_id'], $idRows);

        // 2) Charger les entités candidates puis appliquer le voter LIRE.
        $plateformes = [];
        if ($plateformeIds !== []) {
            $plateformesCandidates = $this->em->getRepository(Plateforme::class)->findBy(['id' => $plateformeIds]);

            // Conserver l'ordre défini par la requête IDs.
            $ordreParId = array_flip($plateformeIds);
            usort(
                $plateformesCandidates,
                static fn(Plateforme $a, Plateforme $b): int => ($ordreParId[$a->getId()] ?? PHP_INT_MAX) <=> ($ordreParId[$b->getId()] ?? PHP_INT_MAX)
            );

            $plateformes = $plateformesCandidates;
        }

        return [
            'plateformes' => $plateformes,
        ];
    }

    function getPlateformeParPage(int $page, ?string $search): array
    {
        if ($this->security->getUser() === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        $offset = ($page - 1) * self::PLATEFORME_PAR_PAGE;

        $conn = $this->em->getConnection();

        // 1) Récupérer les IDs candidats selon la recherche (sans filtrage d'autorisation).
        $idsQb = $conn->createQueryBuilder();
        $idsQb
            ->select('p.id AS plateforme_id', 'ph.nom_officiel AS tri_nom_officiel')
            ->from('plateforme', 'p')
            ->innerJoin('p', 'plateforme_historique', 'ph', 'ph.plateforme_id = p.id')
            ->leftJoin('ph', 'plateforme_historique_personnel', 'pp', 'pp.plateforme_historique_id = ph.id')
            ->leftJoin('pp', 'utilisateur', 'u', 'u.id = pp.utilisateur_id')
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

        $idsQb
            ->groupBy('p.id, ph.nom_officiel')
            ->orderBy('ph.nom_officiel', 'ASC');

        $idRows = $idsQb->executeQuery()->fetchAllAssociative();
        $plateformeIds = array_map(static fn(array $row): int => (int) $row['plateforme_id'], $idRows);

        // 2) Charger les entités candidates puis appliquer le voter LIRE.
        $plateformesAutorisees = [];
        if ($plateformeIds !== []) {
            $plateformesCandidates = $this->em->getRepository(Plateforme::class)->findBy(['id' => $plateformeIds]);

            // Conserver l'ordre défini par la requête IDs.
            $ordreParId = array_flip($plateformeIds);
            usort(
                $plateformesCandidates,
                static fn(Plateforme $a, Plateforme $b): int => ($ordreParId[$a->getId()] ?? PHP_INT_MAX) <=> ($ordreParId[$b->getId()] ?? PHP_INT_MAX)
            );

            $plateformesAutorisees = array_values(array_filter(
                $plateformesCandidates,
                fn(Plateforme $plateforme): bool => $this->security->isGranted(PlateformeVoter::LIRE, $plateforme)
            ));
        }

        // 3) Paginer après application des autorisations pour garder un total cohérent.
        $total = count($plateformesAutorisees);
        $plateformes = array_slice($plateformesAutorisees, $offset, self::PLATEFORME_PAR_PAGE);

        foreach ($plateformes as $plateforme) {
            $plateforme->setPourcentageRemplissage($this->getPlateformePourcentage($plateforme->getLastHistorique()));
        }

        return [
            'plateformes' => $plateformes,
            'page' => $page,
            'pages_total' => ceil($total / self::PLATEFORME_PAR_PAGE),
            'total' => $total
        ];
    }

    function getPlateformeAuxTags(array $tagsIds)
    {
        return $this->em->getRepository(PlateformeTags::class)->findBy(["tagId" => $tagsIds]);
    }

    function getPlateformeAvecDernierHistorique(int $plateformeId, bool $byPassSecurity = false): Plateforme
    {
        if (!$byPassSecurity) {
            if ($this->security->getUser() === null) {
                throw new Exception("Vous devez être authentifié.", 401);
            }
        }

        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("Plateforme non trouvée.", 404);
        }

        if (!$byPassSecurity) {
            if (!$this->security->isGranted(PlateformeVoter::LIRE, $plateforme)) {
                throw new Exception("Vous n'avez pas les droits pour consulter cette plateforme", 403);
            }
        }

        $dernierHistorique = $this->em->getRepository(PlateformeHistorique::class)->findOneBy(
            ['plateforme' => $plateforme],
            ['annee' => 'DESC']
        );

        if (!$dernierHistorique) {
            throw new Exception("Aucun historique trouvé pour cette plateforme.", 404);
        }

        return $dernierHistorique->getPlateforme();
    }

    private function synchroniserInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $value): void
    {
        $payloadParId = [];
        foreach ($value as $instrument) {
            if ($instrument["id"] === null) {
                continue;
            }

            $payloadParId[$instrument["id"]] = $instrument;
        }

        $this->supprimerInstruments($plateformeHisto, $payloadParId, false);
        $this->modifierInstrumentsActuels($plateformeHisto, $payloadParId);
        $this->ajouterInstrumentsActuels($plateformeHisto, $payloadParId);
    }

    private function synchroniserInstrumentsFuturs(PlateformeHistorique $plateformeHisto, array $value): void
    {
        $payloadParId = [];
        foreach ($value as $instrument) {
            if ($instrument["id"] === null) {
                continue;
            }

            $payloadParId[$instrument["id"]] = $instrument;
        }

        $this->supprimerInstruments($plateformeHisto, $payloadParId, true);
        $this->modifierInstrumentsFuturs($plateformeHisto, $payloadParId);
        $this->ajouterInstrumentsFuturs($plateformeHisto, $payloadParId);
    }

    private function supprimerInstruments(PlateformeHistorique $plateformeHisto, array $payloadParId, bool $futur): void
    {
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur() !== $futur) {
                continue;
            }

            if ($instrumentExistant->getId() === null || !isset($payloadParId[$instrumentExistant->getId()])) {
                $this->supprimerDependancesInstrument($instrumentExistant);
                $plateformeHisto->getInstruments()->removeElement($instrumentExistant);
                $this->em->remove($instrumentExistant);
            }
        }
    }

    private function supprimerDependancesInstrument(Instrument $instrument): void
    {
        $financements = $this->em->getRepository(FinancementInstrument::class)->findBy([
            'instrument' => $instrument,
        ]);

        foreach ($financements as $financementInstrument) {
            $this->em->remove($financementInstrument);
        }

        $mutualisationsSortantes = $this->em->getRepository(\App\Entity\InstrumentMutualisation::class)->findBy([
            'instrumentSource' => $instrument,
        ]);

        foreach ($mutualisationsSortantes as $mutualisation) {
            $this->em->remove($mutualisation);
        }

        $mutualisationsEntrantes = $this->em->getRepository(\App\Entity\InstrumentMutualisation::class)->findBy([
            'instrumentCible' => $instrument,
        ]);

        foreach ($mutualisationsEntrantes as $mutualisationEntrante) {
            $this->em->remove($mutualisationEntrante);
        }
    }

    private function modifierInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $payloadParId): void
    {
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getId() === null || !isset($payloadParId[$instrumentExistant->getId()])) {
                continue;
            }

            $payload = $payloadParId[$instrumentExistant->getId()];
            $this->appliquerChampsInstrumentActuel($instrumentExistant, $payload);
            $this->synchroniserMutualisationsInstrumentActuel($instrumentExistant, $payload["mutualise_autre_instrument_ids"] ?? []);
            $this->synchroniserFinancementInstrument($instrumentExistant, $payload["source_financement"] ?? []);
        }
    }

    private function modifierInstrumentsFuturs(PlateformeHistorique $plateformeHisto, array $payloadParId): void
    {
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if (!$instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getId() === null || !isset($payloadParId[$instrumentExistant->getId()])) {
                continue;
            }

            $payload = $payloadParId[$instrumentExistant->getId()];
            $this->appliquerChampsInstrumentFutur($instrumentExistant, $payload);
            $this->synchroniserFinancementInstrument($instrumentExistant, $payload["source_financement"] ?? []);
        }
    }

    private function ajouterInstrumentsActuels(PlateformeHistorique $plateformeHisto, array $payloadParId): void
    {
        $idsExistants = [];
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if ($instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getId() !== null) {
                $idsExistants[$instrumentExistant->getId()] = true;
            }
        }

        foreach ($payloadParId as $id => $instrument) {
            if (isset($idsExistants[$id])) {
                continue;
            }

            $newInstrument = new Instrument();
            $newInstrument->setPlateformeHistorique($plateformeHisto);

            $this->appliquerChampsInstrumentActuel($newInstrument, $instrument);

            $plateformeHisto->addInstrument($newInstrument);
            $this->em->persist($newInstrument);

            $this->synchroniserMutualisationsInstrumentActuel($newInstrument, $instrument["mutualise_autre_instrument_ids"] ?? []);
            $this->synchroniserFinancementInstrument($newInstrument, $instrument["source_financement"] ?? []);

            $idsExistants[$id] = true;
        }
    }

    private function ajouterInstrumentsFuturs(PlateformeHistorique $plateformeHisto, array $payloadParId): void
    {
        $idsExistants = [];
        foreach ($plateformeHisto->getInstruments() as $instrumentExistant) {
            if (!$instrumentExistant->isFutur()) {
                continue;
            }

            if ($instrumentExistant->getId() !== null) {
                $idsExistants[$instrumentExistant->getId()] = true;
            }
        }

        foreach ($payloadParId as $id => $instrument) {
            if (isset($idsExistants[$id])) {
                continue;
            }

            $newInstrument = new Instrument();
            $newInstrument->setPlateformeHistorique($plateformeHisto);

            $this->appliquerChampsInstrumentFutur($newInstrument, $instrument);

            $plateformeHisto->addInstrument($newInstrument);
            $this->em->persist($newInstrument);

            $this->synchroniserFinancementInstrument($newInstrument, $instrument["source_financement"] ?? []);

            $idsExistants[$id] = true;
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

    private function appliquerChampsInstrumentFutur(Instrument $instrumentEntity, array $instrument): void
    {
        $instrumentEntity->setNom($instrument["nom"]);
        $instrumentEntity->setMarque($instrument["marque"]);
        $instrumentEntity->setModele($instrument["modele"]);

        $instrumentEntity->setAnneeAchatEnvisagee($instrument["annee_achat_envisagee"] ?? null);
        $instrumentEntity->setCout($instrument["cout"]);
        $instrumentEntity->setOperation($instrument["operation_id"] ? $this->em->getReference(Operation::class, $instrument["operation_id"]) : null);
        $instrumentEntity->setFutur(true);
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
                $instrumentCible = $this->em->getReference(\App\Entity\Instrument::class, $instrumentMutualisationId);
                $newRecord->setInstrumentCible($instrumentCible);
                $instrumentEntity->addInstrumentMutualisation($newRecord);
                $this->em->persist($newRecord);
            }
        }
    }

    private function normaliserSourcesFinancement(array $sourcesFinancement): array
    {
        $sourcesParFinanceur = [];
        foreach ($sourcesFinancement as $financeur) {
            if (!is_array($financeur) || !array_key_exists("financeur_id", $financeur)) {
                continue;
            }

            $financeurId = (int) $financeur["financeur_id"];
            if ($financeurId <= 0) {
                continue;
            }

            $sourcesParFinanceur[$financeurId] = [
                "financeur_id" => $financeurId,
                "taux" => $financeur["taux"] ?? 0,
            ];
        }

        return $sourcesParFinanceur;
    }

    private function synchroniserFinancementInstrument(Instrument $instrumentEntity, array $sourcesFinancement): void
    {
        $sourcesParFinanceur = $this->normaliserSourcesFinancement($sourcesFinancement);

        $financementExistant = $instrumentEntity->getFinancementInstruments();
        $financeursExistants = [];
        $financeursTraites = [];
        foreach ($financementExistant as $financement) {
            $financeurId = $financement->getFinanceur()->getId();

            // Un seul financement par financeur et par instrument: supprimer les doublons historiques.
            if (isset($financeursTraites[$financeurId])) {
                $this->em->remove($financement);
                continue;
            }

            $financeursTraites[$financeurId] = true;
            $financeursExistants[$financeurId] = true;
            $financementExiste = array_key_exists($financeurId, $sourcesParFinanceur);

            if ($financementExiste) {
                $financement->setTaux((int) $sourcesParFinanceur[$financeurId]["taux"]);
            } else {
                $this->em->remove($financement);
            }
        }

        foreach ($sourcesParFinanceur as $financeurId => $financeur) {
            if (!isset($financeursExistants[$financeurId])) {
                $newFinanceur = $this->em->getReference(Entite::class, $financeurId);
                $newFinancement = new FinancementInstrument();
                $newFinancement->setFinanceur($newFinanceur);
                $newFinancement->setTaux((int) $financeur["taux"]);
                $instrumentEntity->addFinancementInstrument($newFinancement);
                $this->em->persist($newFinancement);
                $financeursExistants[$financeurId] = true;
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
                        $utilisateurId = $personnelExistant->getUtilisateur()?->getId();
                        $existeEncore = false;
                        foreach ($value as $perso) {
                            if ($perso["utilisateur_id"] == $utilisateurId) {
                                $existeEncore = true;
                                break;
                            }
                        }

                        if (!$existeEncore) {
                            $editeur = $personnelExistant->isResponsablePlateforme() || $personnelExistant->isResponsableScientifique() || $personnelExistant->isResponsableTechnique();
                            if (!$editeur) {
                                $plateformeHisto->removePlateformeHistoriquePersonnel($personnelExistant);
                                $this->em->remove($personnelExistant);
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
                            $newRecord->setResponsableScientifique(false);
                            $newRecord->setResponsableTechnique(false);
                            $newRecord->setResponsablePlateforme(false);
                            $newRecord->setQuotite($perso["quotite"]);
                            $plateformeHisto->addPlateformeHistoriquePersonnel($newRecord);
                            $this->em->persist($newRecord);
                        }
                    }
                    break;
                case 'nombre_autres_etudiants':
                    $plateformeHisto->setNombreAutresEtudiants($value);
                    break;
                case 'nombre_apprentis_alternants':
                    $plateformeHisto->setNombreApprentisAlternants($value);
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
                        $posteFuturExistant->setModaliteRecrutement($this->em->getReference(ModaliteRecrutement::class, $poste["modalite_recrutement_id"]));
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
                        $newPosteFutur->setModaliteRecrutement($this->em->getReference(ModaliteRecrutement::class, $poste["modalite_recrutement_id"]));
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
                case "utilisation_developpement_taux":
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
                case "tags_ids":
                    if ($this->security->isGranted(PlateformeVoter::TAG, $plateformeHisto->getPlateforme())) {
                        try {
                            $this->tagService->tagPlateforme($plateformeHisto->getPlateforme()->getId(), $value ?? []);
                        } catch (Exception $e) {
                            // Erreur siliencieuse
                        }
                    }
                    break;
            }
        }

        return $plateformeHisto;
    }
}
