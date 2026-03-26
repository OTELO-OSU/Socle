<?php

namespace App\Service;

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

    function admettreUtilisateur(string $email)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
        if (!$user) throw new Exception("Utilisateur non trouvé", 404);

        if (!$this->security->isGranted('ADMETTRE', $user)) throw new Exception("Vous n'avez pas les droits pour admettre cet utilisateur", 403);

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
            "Votre compte a été admis !",
            "Votre compte a été admis par un administrateur, vous pouvez désormais vous connecter et profiter de toutes les fonctionnalités de Plaire !"
        );
    }
}
