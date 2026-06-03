<?php

namespace App\Service;

use App\Entity\Parametre;
use App\Entity\Utilisateur;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class SaisieService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    public function getAnneeDeSaisieEnCours()
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_ANNEE_SAISIE, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $anneeSaisieEnCours = $this->em->getRepository(Parametre::class)->findOneBy(['cle' => 'annee_saisie_en_cours']);

        return $anneeSaisieEnCours ? $anneeSaisieEnCours->getValeur() : null;
    }

    public function setAnneeDeSaisie(?int $anneeSaisie)
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_ANNEE_SAISIE, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $anneeSaisieEnCours = $this->em->getRepository(Parametre::class)->findOneBy(['cle' => 'annee_saisie_en_cours']);
        if (!$anneeSaisieEnCours) {
            $anneeSaisieEnCours = new Parametre();
            $anneeSaisieEnCours->setCle('annee_saisie_en_cours');
        }
        $anneeSaisieEnCours->setValeur($anneeSaisie);
        $this->em->persist($anneeSaisieEnCours);
        $this->em->flush();
    }
}
