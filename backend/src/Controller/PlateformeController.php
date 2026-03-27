<?php

namespace App\Controller;

use App\Service\PlateformeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlateformeController extends AbstractController
{
    public function __construct(private readonly PlateformeService $plateformeService) {}

    #[Route('/plateforme/creer', methods: ['POST'])]
    public function creerPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['nom'])) return $this->json(['message' => 'Le nom officiel de la plateforme est requis.'], 400);

        $this->plateformeService->creerPlateforme($json['nom']);
        return $this->json(['message' => 'Plateforme créée avec succès.'], 201);
    }
}
