<?php

namespace App\Controller;

use App\Service\UniteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UniteController extends AbstractController
{
    public function __construct(private readonly UniteService $us) {}

    #[Route('/unite', methods: ['GET'])]
    public function getUnite(): Response
    {
        $unites = $this->us->getUnites();
        return $this->json($unites, 200, [], ['groups' => ['unite:list', 'unite:detail', 'utilisateur:list']]);
    }

    #[Route('/unite/directeur', methods: ['PATCH'])]
    public function patchUnite(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!\is_array($data) || !\array_key_exists('unite_id', $data) || !\array_key_exists('utilisateur_id', $data)) {
            return $this->json(['message' => 'Champs requis manquants'], 400);
        }
        $this->us->modifierDirecteurUnite($data['unite_id'], $data['utilisateur_id']);
        return $this->json(['message' => 'Directeur de l\'unité modifié avec succès'], 200);
    }
}
