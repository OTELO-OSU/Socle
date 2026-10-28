<?php

namespace App\Controller;

use App\Service\ExcelService;
use App\Service\OsuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OsuController extends AbstractController
{
    public function __construct(private readonly OsuService $os, private readonly  TranslatorInterface $translator) {}

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

        return $this->json(['message' => $this->translator->trans('osu.succes.dir_osu_change')], 200);
    }

    #[Route('/osu/directeur/delegue', methods: ['PATCH'])]
    public function switchDelegueDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $operation = $json['operation'] ?? null;
        if (!in_array($operation, ['add', 'remove'])) {
            return $this->json(['message' => $this->translator->trans('exception.bad_request.operation_invalide')], 400);
        }

        $message = null;

        $id = $json['delegue_directeur_osu_id'] ?? null;

        if (!$id) {
            return $this->json(['message' => $this->translator->trans('utilisateur.exception.utilisateur_id_manquant')], 400);
        }

        if ($operation === 'add') {
            $this->os->ajouterDelegue($id);
            $message = $this->translator->trans('osu.succes.delegue_osu_ajoute');
        } else {
            $this->os->retirerDelegue($id);
            $message = $this->translator->trans('osu.succes.delegue_osu_retire');
        }

        return $this->json(['message' => $message], 200);
    }
}
