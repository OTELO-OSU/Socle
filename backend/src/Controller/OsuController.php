<?php

namespace App\Controller;

use App\Service\ExcelService;
use App\Service\OsuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OsuController extends AbstractController
{
    public function __construct(private readonly OsuService $os) {}

    #[Route('/osu', methods: ['GET'])]
    public function getOsuAdmin(): Response
    {
        $content = $this->os->getAdminOsu();

        return $this->json($content, 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/osu/directeur', methods: ['PATCH'])]
    public function switchDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $id = $json['directeur_osu_id'] ?? null;
        $this->os->switchDirOsu($id);

        return $this->json(['message' => "Le directeur de l'OSU a été changé."], 200);
    }

    #[Route('/osu/directeur/delegue', methods: ['PATCH'])]
    public function switchDelegueDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $operation = $json['operation'] ?? null;
        if (!in_array($operation, ['add', 'remove'])) {
            return $this->json(['message' => "Opération invalide. L'opération doit être 'add' ou 'remove'."], 400);
        }

        $message = null;

        $id = $json['delegue_directeur_osu_id'] ?? null;
        if ($operation === 'add') {
            $this->os->ajouterDelegue($id);
            $message = "Le délégué du directeur de l'OSU a été ajouté.";
        } else {
            $this->os->retirerDelegue($id);
            $message = "Le délégué du directeur de l'OSU a été retiré.";
        }

        return $this->json(['message' => $message], 200);
    }
}
