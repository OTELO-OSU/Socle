<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\Utilisateur;
use App\Security\Voter\PlateformeVoter;
use App\Security\Voter\UtilisateurVoter;
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

        $users = $this->em->getRepository(Utilisateur::class)->findBy(["admis" => false, "emailValide" => true, "attenteApprobationDU" => false]);
        return array_map(function (Utilisateur $user) {
            return [
                "email" => $user->getEmail(),
                "nom" => $user->getNom(),
                "prenom" => $user->getPrenom(),
                "plateformeDemande" => $user->getUtilisateurInscription()->getPlateformeDemande() ? $user->getUtilisateurInscription()->getPlateformeDemande()->getLastHistorique()->getNomOfficiel() : null,
                "directeurUniteDemande" => $user->getUtilisateurInscription()->isDirecteurUniteDemande(),
            ];
        }, $users);
    }

    function approuverUtilisateur(string $email)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email, "admis" => false, "emailValide" => true]);
        if (!$user) throw new Exception("Utilisateur non trouvé.", 404);

        if (!$this->security->isGranted(UtilisateurVoter::APPROUVER, $user)) throw new Exception("Vous n'avez pas les droits pour approuver cet utilisateur", 403);

        $user->setAdmis(true);

        $userInscription = $user->getUtilisateurInscription();

        if ($userInscription->getPlateformeDemande()) {
            $personnelResponsablePlateforme = new PlateformeHistoriquePersonnel();
            $personnelResponsablePlateforme->setUtilisateur($user);
            $personnelResponsablePlateforme->setPlateformeHistorique($userInscription->getPlateformeDemande()->getLastHistorique());
            $personnelResponsablePlateforme->setResponsablePlateforme(true);
            $this->em->persist($personnelResponsablePlateforme);
        }

        if ($userInscription->isDirecteurUniteDemande()) {
            $unite = $userInscription->getUtilisateur()->getUnite();
            $unite->setDirecteur($user);
            $this->em->persist($unite);
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
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email, "admis" => false, "emailValide" => true]);
        if (!$user) throw new Exception("Utilisateur non trouvé.", 404);

        if (!$this->security->isGranted(UtilisateurVoter::APPROUVER, $user)) throw new Exception("Vous n'avez pas les droits pour refuser cet utilisateur", 403);

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
        /** @var Plateforme $plateforme */
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("La plateforme n'existe pas.", 404);
        }

        // Vérification des permissions de l'utilisateur
        if (!$this->security->isGranted(PlateformeVoter::AFFILIER, $plateforme)) throw new Exception("Vous n'avez pas les permissions nécessaires pour modifier les responsables de la plateforme.", 403);

        $plateforme = $plateforme->getLastHistorique();
        if (!$plateforme) {
            throw new Exception("La plateforme n'existe pas.", 404);
        }

        // Récupération des utilisateurs à affilier
        $responsablesScientifiques = $responsableScientifiquesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsableScientifiquesIds]) : [];
        $responsablesTechniques = $responsableTechniquesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsableTechniquesIds]) : [];
        $responsablesPlateformes = $responsablePlateformesIds ? $this->em->getRepository(Utilisateur::class)->findBy(['id' => $responsablePlateformesIds]) : [];

        $personnels = $plateforme->getPlateformeHistoriquePersonnels();
        foreach ($personnels as $personnel) {
            if ($personnel->isResponsableScientifique() && !in_array($personnel->getUtilisateur(), $responsablesScientifiques)) {
                $personnel->setResponsableScientifique(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateur()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables scientifiques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }

            if ($personnel->isResponsableTechnique() && !in_array($personnel->getUtilisateur(), $responsablesTechniques)) {
                $personnel->setResponsableTechnique(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateur()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables techniques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }

            if ($personnel->isResponsablePlateforme() && !in_array($personnel->getUtilisateur(), $responsablesPlateformes)) {
                $personnel->setResponsablePlateforme(false);
                $this->em->persist($personnel);
                $this->mailerService->sendEmailToUser(
                    $personnel->getUtilisateur()->getEmail(),
                    "Changement de rôle",
                    "Vous avez été retiré des responsables de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
                );
            }
        }

        foreach ($responsablesScientifiques as $responsableScientifique) {
            $personnel = $this->trouverOuCreerPersonnelPlateforme($plateforme, $responsableScientifique);
            $personnel->setResponsableScientifique(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateur()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables scientifiques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        foreach ($responsablesTechniques as $responsableTechnique) {
            $personnel = $this->trouverOuCreerPersonnelPlateforme($plateforme, $responsableTechnique);
            $personnel->setResponsableTechnique(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateur()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables techniques de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        foreach ($responsablesPlateformes as $responsablePlateforme) {
            $personnel = $this->trouverOuCreerPersonnelPlateforme($plateforme, $responsablePlateforme);
            $personnel->setResponsablePlateforme(true);
            $this->em->persist($personnel);
            $this->mailerService->sendEmailToUser(
                $personnel->getUtilisateur()->getEmail(),
                "Changement de rôle",
                "Vous avez été promu responsables plateformes de la plateforme \"" . $plateforme->getNomOfficiel() . "\" par un administrateur."
            );
        }

        $this->em->flush();
    }

    private function trouverOuCreerPersonnelPlateforme(PlateformeHistorique $plateforme, Utilisateur $utilisateur): PlateformeHistoriquePersonnel
    {
        $personnel = $plateforme->getPlateformeHistoriquePersonnels()->filter(function (PlateformeHistoriquePersonnel $personnel) use ($utilisateur) {
            return $personnel->getUtilisateur()?->getId() === $utilisateur->getId();
        })->first();

        if ($personnel instanceof PlateformeHistoriquePersonnel) {
            return $personnel;
        }

        $personnel = new PlateformeHistoriquePersonnel();
        $personnel->setUtilisateur($utilisateur);
        $plateforme->addPlateformeHistoriquePersonnel($personnel);

        return $personnel;
    }
}
