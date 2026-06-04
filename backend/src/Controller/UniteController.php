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

    #[Route('/unite/directeur/delegue', methods: ['PATCH'])]
    public function switchDelegueDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $operation = $json['operation'] ?? null;
        if (!in_array($operation, ['add', 'remove'])) {
            return $this->json(['message' => "Opération invalide. L'opération doit être 'add' ou 'remove'."], 400);
        }

        $uniteId = $json['unite_id'] ?? null;
        if (!$uniteId) {
            return $this->json(['message' => "L'ID de l'unité est requis."], 400);
        }

        $message = null;

        $id = $json['delegue_directeur_unite_id'] ?? null;

        if (!$id) {
            return $this->json(['message' => "L'ID de l'utilisateur est requis."], 400);
        }

        if ($operation === 'add') {
            $this->us->ajouterDelegue($id, $uniteId);
            $message = "Le délégué du directeur de l'OSU a été ajouté.";
        } else {
            $this->us->supprimerDelegue($id, $uniteId);
            $message = "Le délégué du directeur de l'OSU a été retiré.";
        }

        return $this->json(['message' => $message], 200);
    }
}
