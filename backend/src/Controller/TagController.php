<?php

namespace App\Controller;

use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TagController extends AbstractController
{
    public function __construct(private readonly TagService $ts) {}

    #[Route('/tags', methods: ['GET'])]
    public function getTags(): Response
    {
        $tags = $this->ts->getTags();

        return $this->json($tags, 200);
    }

    #[Route('/plateforme/tags', methods: ['GET'])]
    public function getPlateformeTags(Request $request): Response
    {
        $plateformeId = $request->query->get('id');
        $tags = $this->ts->getPlateformeTags($plateformeId);

        return $this->json($tags, 200);
    }

    #[Route('/plateforme/tags', methods: ['PATCH'])]
    public function patchPlateformeTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $plateformeId = $json['id'] ?? null;
        $tagIds = $json['tagIds'] ?? [];
        $this->ts->tagPlateforme($plateformeId, $tagIds);
        return $this->json(['message' => 'Tags modifiés avec succès.'], 200);
    }
}
