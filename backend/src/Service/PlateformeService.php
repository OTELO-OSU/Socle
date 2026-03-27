<?php

namespace App\Service;

use App\Entity\Parametre;
use App\Entity\Plateforme;
use App\Entity\PlateformeEditeur;
use App\Entity\PlateformeHistorique;
use App\Entity\Utilisateur;
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

    function creerPlateforme(string $nom)
    {
        if (!$this->security->isGranted('CREER', Plateforme::class)) throw new Exception("Vous n'avez pas les droits pour créer une plateforme", 403);

        $plateforme = new Plateforme();
        $plateformeHistorique = new PlateformeHistorique();
        $plateformeHistorique->setPlateforme($plateforme);
        $plateformeHistorique->setNomOfficiel($nom);
        $plateformeHistorique->setAnnee($this->getAnneeDeSaisieEnCours());

        $this->em->persist($plateforme);
        $this->em->persist($plateformeHistorique);
        $this->em->flush();

        return $plateforme;
    }
}
