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
use App\Entity\Operation;
use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeEditeur;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\PlateformeHistoriqueTutelle;
use App\Entity\PosteFuturBap;
use App\Entity\PosteFuturEmploiType;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class PlateformeService
{
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
        $plateformeHisto = $this->em->getRepository(PlateformeHistorique::class)->find($dto->id);
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
                case 'tutelle_principale':
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
                case 'tutelle_secondaire':
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
                case 'espace_laboratoire':
                    $plateformeHisto->setEspaceLaboratoire($value);
                    break;
                case 'personnel':
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
                            if ($personnelExistant->getUtilisateurId()->getId() == $nouveauItilisateurId) {
                                $existeDeja = true;
                                break;
                            }
                        }
                        if (!$existeDeja) {
                            $newRecord = new PlateformeHistoriquePersonnel();
                            $newRecord->setPlateformeHistorique($plateformeHisto);
                            $utilisateur = $this->em->getReference(Utilisateur::class, $nouveauItilisateurId);
                            $newRecord->setUtilisateurId($utilisateur);
                            $newRecord->setResponsableScientifique($perso["responsable_scientifique"]);
                            $newRecord->setResponsableTechnique($perso["responsable_technique"]);
                            $typeContrat = $this->em->getReference(TypeContrat::class, $perso["type_contrat_id"]);
                            $newRecord->setTypeContrat($typeContrat);
                            $newRecord->setQuotite($perso["quotite"]);
                            $plateformeHisto->addPlateformeHistoriquePersonnel($newRecord);
                            $this->em->persist($newRecord);
                        }
                    }
                    break;
                case 'postes_futurs':
                    foreach ($plateformeHisto->getPosteFuturs() as $posteFuturExistant) {
                        $numerateur = $posteFuturExistant->getRangNumerateur();
                        $denominateur = $posteFuturExistant->getRangDenominateur();
                        $existeDeja = false;
                        foreach ($value as $poste) {
                            if ($poste["rang_numerateur"] == $numerateur && $poste["rang_denominateur"] == $denominateur) {
                                $existeDeja = true;

                                // Overrider les champs du poste futur
                                $posteFuturExistant->setCorps($this->em->getReference(Corps::class, $poste["corps_id"]));
                                $posteFuturExistant->setEmployeur($this->em->getReference(Entite::class, $poste["employeur_id"]));
                                $posteFuturExistant->setQuotite($poste["quotite"]);

                                // Supprimer les baps en trop
                                $nouvellesBaps = $poste["bap"];
                                foreach ($posteFuturExistant->getPosteFuturBaps() as $bapExistant) {
                                    $bapId = $bapExistant->getBap()->getId();
                                    $bapExiste = false;
                                    foreach ($nouvellesBaps as $bap) {
                                        if ($bap == $bapId) {
                                            $bapExiste = true;
                                            break;
                                        }
                                    }
                                    if ($bapExiste) {
                                        $this->em->remove($bapExistant);
                                    }
                                }

                                // Ajouter les nouveaux baps
                                foreach ($poste["bap"] as $bap) {
                                    $bapExiste = false;
                                    foreach ($posteFuturExistant->getPosteFuturBaps() as $bapExistant) {
                                        if ($bapExistant->getBap()->getId() == $bap) {
                                            $bapExiste = true;
                                            break;
                                        }
                                    }
                                    if (!$bapExiste) {
                                        $newBap = $this->em->getReference(Bap::class, $bap);
                                        $newPivot = new PosteFuturBap();
                                        $newPivot->setBap($newBap);
                                        $newPivot->setPosteFutur($posteFuturExistant);
                                        $this->em->persist($newPivot);
                                    }
                                }

                                // Supprimer les emplois type en trop
                                $nouveauxEt = $poste["emploi_type"];
                                foreach ($posteFuturExistant->getPosteFuturEmploiTypes() as $etExistant) {
                                    $etId = $etExistant->getEmploiType()->getId();
                                    $etExiste = false;
                                    foreach ($nouveauxEt as $et) {
                                        if ($et == $etId) {
                                            $etExiste = true;
                                            break;
                                        }
                                    }
                                    if ($etExiste) {
                                        $this->em->remove($etExistant);
                                    }
                                }

                                // Ajouter les nouveaux emplois types
                                foreach ($poste["emploi_type"] as $et) {
                                    $etExiste = false;
                                    foreach ($posteFuturExistant->getPosteFuturEmploiTypes() as $etExistant) {
                                        if ($etExistant->getEmploiType()->getId() == $et) {
                                            $etExiste = true;
                                            break;
                                        }
                                    }
                                    if (!$etExiste) {
                                        $newEt = $this->em->getReference(EmploiType::class, $et);
                                        $newPivot = new PosteFuturEmploiType();
                                        $newPivot->setEmploiType($newEt);
                                        $newPivot->setPosteFutur($posteFuturExistant);
                                        $this->em->persist($newPivot);
                                    }
                                }
                            }
                        }
                    }
                    break;
                case "instrument_actuel":
                    $instrumentsActuels = $plateformeHisto->getInstruments();
                    foreach ($instrumentsActuels as $instrumentExistant) {
                        // Ne traiter ici que les instruments actuels.
                        if ($instrumentExistant->isFutur()) {
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
                                if ($instrument["annee_acquisition"] !== null) {
                                    $instrumentExistant->setAnneeAcquisition(new \DateTime($instrument["annee_acquisition"]));
                                } else {
                                    $instrumentExistant->setAnneeAcquisition(null);
                                }
                                $instrumentExistant->setCout($instrument["cout"]);
                                $instrumentExistant->setValeurRemplacement($instrument["valeur_remplacement"]);
                                $instrumentExistant->setDureeUtilisation($instrument["duree_utilisation"]);
                                $instrumentExistant->setFutur(false);

                                $instrumentExistant->setContratMaintenance($this->em->getReference(ContratMaintenance::class, $instrument["type_contrat_id"]));
                                $instrumentExistant->setCommentaireNatureContrat($instrument["commentaire_nature_contrat"]);
                                $instrumentExistant->setFournisseurContrat($instrument["fournisseur_contrat"]);
                                $instrumentExistant->setMutualiseAutreInstrument($instrument["mutualisation_autre_instrument"]);
                                $instrumentExistant->setMutualiseSite($instrument["mutualisation_site"]);
                                $instrumentExistant->setMutualiseUmrOsu($instrument["mutualisation_umr_osu"]);
                                $instrumentExistant->setCoutMaintenance($instrument["cout_maintenance"]);

                                $mutualisationAutreInstrumentDetailleActuelle = $instrumentExistant->getInstrumentMutualisations();
                                foreach ($mutualisationAutreInstrumentDetailleActuelle as $record) {
                                    $instrumentMutualisationId = $record->getInstrumentCible()->getId();
                                    $existeDeja = false;
                                    foreach ($instrument["mutualisation_autre_instrument_detaille"] as $mutualisation) {
                                        if ($mutualisation == $instrumentMutualisationId) {
                                            // Le record de mutualisation existe déjà, pas besoin de le recréer
                                            $existeDeja = true;
                                            break;
                                        }
                                    }
                                    if (!$existeDeja) {
                                        // Le record de mutualisation n'existe plus, il doit être supprimé
                                        $this->em->remove($record);
                                    }
                                }

                                foreach ($instrument["mutualisation_autre_instrument_detaille"] as $instrumentMutualisationId) {
                                    $existeDeja = false;
                                    foreach ($instrumentExistant->getInstrumentMutualisations() as $record) {
                                        if ($record->getInstrumentCible()->getId() == $instrumentMutualisationId) {
                                            // Le record de mutualisation existe déjà, pas besoin de le recréer
                                            $existeDeja = true;
                                            break;
                                        }
                                    }
                                    if (!$existeDeja) {
                                        // Le record de mutualisation n'existe pas encore, il doit être créé
                                        $newRecord = new \App\Entity\InstrumentMutualisation();
                                        $newRecord->setInstrumentSource($instrumentExistant);
                                        $instrumentCible = $this->em->getReference(\App\Entity\Instrument::class, $instrumentMutualisationId);
                                        $newRecord->setInstrumentCible($instrumentCible);
                                        $this->em->persist($newRecord);
                                    }
                                }

                                $financementExistant = $instrumentExistant->getFinancementInstruments();
                                foreach ($financementExistant as $financement) {
                                    $financeurId = $financement->getFinanceur()->getId();
                                    $financementExiste = false;
                                    foreach ($instrument["source_financement"] as $financeur) {
                                        if ($financeur["financeur_id"] == $financeurId) {
                                            $financementExiste = true;

                                            // Overrider les champs du financement
                                            $financement->setTaux($financeur["taux"] ?? $financeur["pourcentage"]);
                                            break;
                                        }
                                    }
                                    if (!$financementExiste) {
                                        $this->em->remove($financement);
                                    }
                                }

                                // Ajouter les nouveaux financements
                                foreach ($instrument["source_financement"] as $financeur) {
                                    $financeurExiste = false;
                                    foreach ($instrumentExistant->getFinancementInstruments() as $financement) {
                                        if ($financement->getFinanceur()->getId() == $financeur["financeur_id"]) {
                                            $financeurExiste = true;
                                            break;
                                        }
                                    }
                                    if (!$financeurExiste) {
                                        $newFinanceur = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                                        $newFinancement = new \App\Entity\FinancementInstrument();
                                        $newFinancement->setFinanceur($newFinanceur);
                                        $newFinancement->setInstrument($instrumentExistant);
                                        $newFinancement->setTaux($financeur["taux"] ?? $financeur["pourcentage"]);
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
                            if ($instrument["annee_acquisition"] !== null) {
                                $newInstrument->setAnneeAcquisition(new \DateTime($instrument["annee_acquisition"]));
                            } else {
                                $newInstrument->setAnneeAcquisition(null);
                            }
                            $newInstrument->setCout($instrument["cout"]);
                            $newInstrument->setValeurRemplacement($instrument["valeur_remplacement"]);
                            $newInstrument->setDureeUtilisation($instrument["duree_utilisation"]);
                            $newInstrument->setFutur(false);

                            $newInstrument->setContratMaintenance($this->em->getReference(ContratMaintenance::class, $instrument["contrat_maintenance_id"]));
                            $newInstrument->setCommentaireNatureContrat($instrument["commentaire_nature_contrat"]);
                            $newInstrument->setFournisseurContrat($instrument["fournisseur_contrat"]);
                            $newInstrument->setMutualiseAutreInstrument($instrument["mutualisation_autre_instrument"]);
                            $newInstrument->setMutualiseSite($instrument["mutualisation_site"]);
                            $newInstrument->setMutualiseUmrOsu($instrument["mutualisation_umr_osu"]);
                            $newInstrument->setCoutMaintenance($instrument["cout_maintenance"]);

                            $plateformeHisto->addInstrument($newInstrument);
                            $this->em->persist($newInstrument);

                            if (array_key_exists("mutualisation_autre_instrument_detaille", $instrument)) {
                                foreach ($instrument["mutualisation_autre_instrument_detaille"] as $instrumentMutualisationId) {
                                    $instrumentCible = $this->em->getReference(\App\Entity\Instrument::class, $instrumentMutualisationId);
                                    $newRecord = new \App\Entity\InstrumentMutualisation();
                                    $newRecord->setInstrumentSource($newInstrument);
                                    $newRecord->setInstrumentCible($instrumentCible);
                                    $this->em->persist($newRecord);
                                }
                            }

                            foreach ($instrument["source_financement"] as $financeur) {
                                $financeurEntite = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                                $newFinancement = new FinancementInstrument();
                                $newFinancement->setFinanceur($financeurEntite);
                                $newFinancement->setInstrument($newInstrument);
                                $newFinancement->setTaux($financeur["taux"]);
                                $this->em->persist($newFinancement);
                            }
                        }
                    }

                    break;
                case "instrument_futur":
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
                                foreach ($financementExistant as $financement) {
                                    $financeurId = $financement->getFinanceur()->getId();
                                    $financementExiste = false;
                                    foreach ($instrument["source_financement"] as $financeur) {
                                        if ($financeur["financeur_id"] == $financeurId) {
                                            $financementExiste = true;

                                            // Overrider les champs du financement
                                            $financement->setTaux($financeur["taux"]);
                                            break;
                                        }
                                    }
                                    if ($financementExiste) {
                                        $this->em->remove($financement);
                                    }
                                }

                                // Ajouter les nouveaux financements
                                foreach ($instrument["source_financement"] as $financeur) {
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

                            foreach ($instrument["source_financement"] as $financeur) {
                                $financeurEntite = $this->em->getReference(Entite::class, $financeur["financeur_id"]);
                                $newFinancement = new \App\Entity\FinancementInstrument();
                                $newFinancement->setFinanceur($financeurEntite);
                                $newFinancement->setInstrument($newInstrument);
                                $newFinancement->setTaux($financeur["taux"]);
                                $this->em->persist($newFinancement);
                            }
                        }
                    }
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
                            if ($detail["tutelle_id"] == $entiteId) {
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
                            $entiteId = $detail["tutelle_id"];
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
            }
        }

        return $plateformeHisto;
    }
}
