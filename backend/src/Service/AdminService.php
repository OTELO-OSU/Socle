<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\Utilisateur;
use App\Security\Voter\PlateformeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class AdminService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerService $mailerService,
        private readonly Security $security
    ) {}

    function getUtilisateurEnAttenteDeValidation(): array
    {
        if (!$this->security->isGranted('GetEnAttenteDeValidation', Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour voir les utilisateurs en attente de validation", 403);

        $users = $this->em->getRepository(Utilisateur::class)->findBy(["admis" => false]);
        return array_map(function (Utilisateur $user) {
            return [
                "email" => $user->getEmail(),
                "nom" => $user->getNom(),
                "prenom" => $user->getPrenom(),
                "plateformeDemande" => $user->getUtilisateurInscription()->getPlateformeDemande() ? $user->getUtilisateurInscription()->getPlateformeDemande()->getLastHistorique()->getNomOfficiel() : null,
                "uniteDemande" => $user->getUtilisateurInscription()->getUniteDemande() ? $user->getUtilisateurInscription()->getUniteDemande()->getNom() : null,
                "osuDemande" => $user->getUtilisateurInscription()->isOsuDemande(),
            ];
        }, $users);
    }

    function approuverUtilisateur(string $email)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email, "admis" => false]);
        if (!$user) throw new Exception("Utilisateur non trouvé.", 404);

        if (!$this->security->isGranted('APPROUVER', $user)) throw new Exception("Vous n'avez pas les droits pour approuver cet utilisateur", 403);

        $user->setAdmis(true);

        $userInscription = $user->getUtilisateurInscription();

        if ($userInscription->getPlateformeDemande()) {
            $personnelResponsablePlateforme = new PlateformeHistoriquePersonnel();
            $personnelResponsablePlateforme->setUtilisateurId($user);
            $personnelResponsablePlateforme->setPlateformeHistorique($userInscription->getPlateformeDemande()->getLastHistorique());
            $personnelResponsablePlateforme->setResponsablePlateforme(true);
            $this->em->persist($personnelResponsablePlateforme);
        }

        if ($userInscription->getUniteDemande()) {
            $unite = $userInscription->getUniteDemande();
            $unite->setDirecteur($user);
            $this->em->persist($unite);
        }

        if ($userInscription->isOsuDemande()) {
            $user->setRoles(array_merge($user->getRoles(), ['ROLE_DIR_OSU']));
        }


        $this->em->persist($user);
        $this->em->flush();

        $this->mailerService->sendEmailToUser(
            $user->getEmail(),
            "Votre compte a été approuvé !",
            "Votre compte a été approuvé par un administrateur, vous pouvez désormais vous connecter et profiter de toutes les fonctionnalités de Plaire !"
        );
    }

    function refuserUtilisateur(string $email)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email, "admis" => false]);
        if (!$user) throw new Exception("Utilisateur non trouvé.", 404);

        if (!$this->security->isGranted('APPROUVER', $user)) throw new Exception("Vous n'avez pas les droits pour refuser cet utilisateur", 403);

        $this->mailerService->sendEmailToUser(
            $user->getEmail(),
            "Votre compte a été refusé !",
            "Votre compte a été refusé par un administrateur."
        );

        $this->em->remove($user);
        $this->em->flush();
    }

    function changeResponsable(int $plateformeId, ?array $responsableScientifiquesIds, ?array $responsableTechniquesIds, ?array $responsablePlateformesIds)
    {
        // Vérification des permissions de l'utilisateur
        if (!$this->security->isGranted(PlateformeVoter::AFFILIER, Plateforme::class)) throw new Exception("Vous n'avez pas les permissions nécessaires pour modifier les responsables de la plateforme.", 403);

        // Est-ce que la plateforme existe ?
        /** @var PlateformeHistorique $plateforme */
        $plateforme = $this->em->getRepository(PlateformeHistorique::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("La plateforme n'existe pas.", 404);
        }

        // Récupération des utilisateurs à affilier
        $responsablesScientifiques = $responsableScientifiquesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsableScientifiquesIds]) : [];
        $responsablesTechniques = $responsableTechniquesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsableTechniquesIds]) : [];
        $responsablesPlateformes = $responsablePlateformesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsablePlateformesIds]) : [];

        $personnels = $plateforme->getPlateformeHistoriquePersonnels();
        foreach ($personnels as $personnel) {
            if ($personnel->isResponsableScientifique() && !in_array($personnel->getUtilisateurId(), $responsablesScientifiques)) {
                $personnel->setResponsableScientifique(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateurId()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables scientifiques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }

            if ($personnel->isResponsableTechnique() && !in_array($personnel->getUtilisateurId(), $responsablesTechniques)) {
                $personnel->setResponsableTechnique(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateurId()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables techniques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }

            if ($personnel->isResponsablePlateforme() && !in_array($personnel->getUtilisateurId(), $responsablesPlateformes)) {
                $personnel->setResponsablePlateforme(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateurId()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }
        }

        foreach ($responsablesScientifiques as $responsableScientifique) {
            $personnel = $plateforme->getPlateformeHistoriquePersonnels()->filter(function (PlateformeHistoriquePersonnel $personnel) use ($responsableScientifique) {
                return $personnel->getUtilisateurId()?->getId() === $responsableScientifique->getId();
            })->first();
            if (!$personnel) {
                $personnel = new PlateformeHistoriquePersonnel();
                $personnel->setUtilisateurId($responsableScientifique);
                $personnel->setPlateformeHistorique($plateforme);
            }
            $personnel->setResponsableScientifique(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateurId()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables scientifiques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        foreach ($responsablesTechniques as $responsableTechnique) {
            $personnel = $plateforme->getPlateformeHistoriquePersonnels()->filter(function (PlateformeHistoriquePersonnel $personnel) use ($responsableTechnique) {
                return $personnel->getUtilisateurId()?->getId() === $responsableTechnique->getId();
            })->first();
            if (!$personnel) {
                $personnel = new PlateformeHistoriquePersonnel();
                $personnel->setUtilisateurId($responsableTechnique);
                $personnel->setPlateformeHistorique($plateforme);
            }
            $personnel->setResponsableTechnique(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateurId()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables techniques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        foreach ($responsablesPlateformes as $responsablePlateforme) {
            $personnel = $plateforme->getPlateformeHistoriquePersonnels()->filter(function (PlateformeHistoriquePersonnel $personnel) use ($responsablePlateforme) {
                return $personnel->getUtilisateurId()?->getId() === $responsablePlateforme->getId();
            })->first();
            if (!$personnel) {
                $personnel = new PlateformeHistoriquePersonnel();
                $personnel->setUtilisateurId($responsablePlateforme);
                $personnel->setPlateformeHistorique($plateforme);
            }
            $personnel->setResponsablePlateforme(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateurId()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables plateformes de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        $this->em->flush();
    }
}
