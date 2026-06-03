<?php

namespace App\Controller;

use App\Service\ExcelService;
use App\Service\OsuService;
use App\Service\SaisieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SaisieController extends AbstractController
{
    public function __construct(private readonly SaisieService $ss) {}

    #[Route('/saisie', methods: ['GET'])]
    public function getOsuAdmin(): Response
    {
        $content = $this->ss->getAnneeDeSaisieEnCours();

        return $this->json(['annee' => $content], 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/saisie', methods: ['PATCH'])]
    public function switchDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $annee = $json['annee'] ?? null;

        if (!$annee) {
            return $this->json(['message' => "L'année de saisie est requise."], 400);
        }

        //Vérifier qu'il y a 4 chiffres
        if (!preg_match('/^\d{4}$/', $annee)) {
            return $this->json(['message' => "L'année de saisie doit être au format YYYY."], 400);
        }

        $this->ss->setAnneeDeSaisie($annee);

        return $this->json(['message' => "L'année de saisie a été changée."], 200);
    }
}
