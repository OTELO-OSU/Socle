<?php

namespace App\Controller;

use App\Service\DictionnaireService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DictionnaireController extends AbstractController
{
    public function __construct(private readonly DictionnaireService $ds) {}

    #[Route('/dictionnaire/unites', methods: ['GET'])]
    public function getUnites(): Response
    {
        $unites = $this->ds->getUnite();

        return $this->json($unites, 200, [], ['groups' => 'unite:list']);
    }

    #[Route('/dictionnaire/corps', methods: ['GET'])]
    public function getCorps(): Response
    {
        $corps = $this->ds->getCorps();

        return $this->json($corps, 200, [], ['groups' => 'corps:list']);
    }

    #[Route('/dictionnaire/type_contrat', methods: ['GET'])]
    public function getTypeContrats(): Response
    {
        $typeContrats = $this->ds->getTypeContrat();

        return $this->json($typeContrats, 200, [], ['groups' => 'type_contrat:list']);
    }

    #[Route('/dictionnaire/villes', methods: ['GET'])]
    public function getVilles(): Response
    {
        $villes = $this->ds->getVille();
        return $this->json($villes, 200, [], ['groups' => 'ville:list']);
    }
}
