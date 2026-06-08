<?php

namespace App\Service;

use App\Entity\Entite;
use App\Entity\Utilisateur;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class UniteService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerService $mailerService,
        private readonly Security $security
    ) {}

    function getUnites(): array
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        if ($this->security->isGranted(UtilisateurVoter::GET_TOUTE_UNITE, Utilisateur::class)) {
            $unites = $this->em->getRepository(Entite::class)->findBy(['laboratoire' => true]);
        } else {
            $array = [];
            // Si DU
            foreach ($user->getEntites() as $e) {
                $array[] = $e;
            }
            // Si delegue
            foreach ($user->getDeleguesDirecteur() as $d) {
                /** @var Entite $d */
                // Vérifier que l'unité n'est pas déjà dans l'array
                if (!in_array($d, $array)) {
                    $array[] = $d;
                }
            }

            if (empty($array)) {
                throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
            }

            $unites = $array;
        }

        return $unites;
    }

    function modifierDirecteurUnite(int $uniteId, ?int $utilisateurId)
    {
        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DU, Utilisateur::class)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        /** @var Entite $unite */
        $unite = $this->em->getRepository(Entite::class)->find($uniteId);
        if (!$unite || !$unite->isLaboratoire()) {
            throw new Exception("L'unité spécifiée est invalide.", 400);
        }

        $utilisateur = null;
        if ($utilisateurId !== null) {
            /** @var Utilisateur|null $utilisateur */
            $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
            if (!$utilisateur) {
                throw new Exception("L'utilisateur spécifié est invalide.", 400);
            }
        }

        $unite->setDirecteur($utilisateur);
        $this->em->persist($unite);
        $this->em->flush();
    }

    function ajouterDelegue(int $utilisateurId, int $uniteId)
    {
        /** @var Entite|null $uniteEntity */
        $uniteEntity = $this->em->getRepository(Entite::class)->find($uniteId);
        if (!$uniteEntity || !$uniteEntity->isLaboratoire()) {
            throw new Exception("L'unité spécifiée est invalide.", 400);
        }

        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DU, $uniteEntity)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            throw new Exception("L'utilisateur spécifié est invalide.", 400);
        }

        $uniteEntity->addDelegueDirecteur($utilisateur);
        $this->em->flush();
    }

    function supprimerDelegue(int $utilisateurId, int $uniteId)
    {
        /** @var Entite|null $uniteEntity */
        $uniteEntity = $this->em->getRepository(Entite::class)->find($uniteId);
        if (!$uniteEntity || !$uniteEntity->isLaboratoire()) {
            throw new Exception("L'unité spécifiée est invalide.", 400);
        }

        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DU, $uniteEntity)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            throw new Exception("L'utilisateur spécifié est invalide.", 400);
        }

        $uniteEntity->removeDelegueDirecteur($utilisateur);
        $this->em->flush();
    }
}
