<?php

namespace App\Service;

use App\Entity\Entite;
use App\Entity\Utilisateur;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniteService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly TranslatorInterface $translator
    ) {}

    function getUnites(): array
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('auth.exception.non_connecte'));
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
                throw new AccessDeniedHttpException($this->translator->trans('unite.exception.gerer_unite'));
            }

            $unites = $array;
        }

        return $unites;
    }

    function modifierDirecteurUnite(int $uniteId, ?int $utilisateurId)
    {
        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DU, Utilisateur::class)) {
            throw new AccessDeniedHttpException($this->translator->trans('unite.exception.gerer_du'));
        }

        /** @var Entite $unite */
        $unite = $this->em->getRepository(Entite::class)->find($uniteId);
        if (!$unite || !$unite->isLaboratoire()) {
            throw new NotFoundHttpException($this->translator->trans('unite.exception.unite_nf'));
        }

        $utilisateur = null;
        if ($utilisateurId !== null) {
            /** @var Utilisateur|null $utilisateur */
            $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
            if (!$utilisateur) {
                throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_nf'));
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
            throw new NotFoundHttpException($this->translator->trans('unite.exception.unite_nf'));
        }

        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DU, $uniteEntity)) {
            throw new AccessDeniedHttpException($this->translator->trans('unite.exception.gerer_delegue_du'));
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_nf'));
        }

        $uniteEntity->addDelegueDirecteur($utilisateur);
        $this->em->flush();
    }

    function supprimerDelegue(int $utilisateurId, int $uniteId)
    {
        /** @var Entite|null $uniteEntity */
        $uniteEntity = $this->em->getRepository(Entite::class)->find($uniteId);
        if (!$uniteEntity || !$uniteEntity->isLaboratoire()) {
            throw new NotFoundHttpException($this->translator->trans('unite.exception.unite_nf'));
        }

        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DU, $uniteEntity)) {
            throw new AccessDeniedHttpException($this->translator->trans('unite.exception.gerer_delegue_du'));
        }

        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_nf'));
        }

        $uniteEntity->removeDelegueDirecteur($utilisateur);
        $this->em->flush();
    }
}
