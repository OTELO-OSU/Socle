<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeEditeur;
use App\Entity\Utilisateur;
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
            $plateformeEditeur = new PlateformeEditeur();
            $plateformeEditeur->setUtilisateur($user);
            $plateformeEditeur->setPlateforme($userInscription->getPlateformeDemande());
            $this->em->persist($plateformeEditeur);
        }

        if ($userInscription->getUniteDemande()) {
            $unite = $userInscription->getUniteDemande();
            $unite->setDirecteur($user);
            $this->em->persist($unite);
        }

        if ($userInscription->isOsuDemande()) {
            // Todo: voir l'ensemble si directeur d'osu ne se résumerait pas à un admin
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

    function affilierUtilisateurPlateforme(string $email, int $plateformeId)
    {

        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) throw new Exception("Plateforme non trouvée.", 404);


        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
        if (!$user) throw new Exception("Utilisateur non trouvé.", 404);

        if (!$this->security->isGranted('AFFILIER', Plateforme::class)) throw new Exception("Vous n'avez pas les droits pour affilier un utilisateur à une plateforme", 403);

        if ($this->em->getRepository(PlateformeEditeur::class)->findOneBy(["utilisateur" => $user, "plateforme" => $plateforme])) {
            throw new Exception("L'utilisateur est déjà affilié à cette plateforme.", 400);
        }

        $plateformeEditeur = new PlateformeEditeur();
        $plateformeEditeur->setUtilisateur($user);
        $plateformeEditeur->setPlateforme($plateforme);
        $this->em->persist($plateformeEditeur);
        $this->em->flush();

        $this->mailerService->sendEmailToUser(
            $user->getEmail(),
            "Vous avez été affilié à une plateforme !",
            "Vous avez été affilié à une plateforme par un administrateur, vous pouvez désormais accéder à cette plateforme et profiter de toutes les fonctionnalités qui y sont associées !"
        );
    }
}
