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
        $plateformeId = $request->query->get('id', null);
        if ($plateformeId === null) {
            return $this->json(['message' => 'L\'ID de la plateforme est requis.'], 400);
        }
        $tags = $this->ts->getPlateformeTags($plateformeId);

        return $this->json($tags, 200);
    }

    #[Route('/plateforme/tags', methods: ['PATCH'])]
    public function patchPlateformeTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $plateformeId = $json['id'] ?? null;
        $tagIds = $json['tagIds'] ?? [];
        if ($plateformeId === null) {
            return $this->json(['message' => 'L\'ID de la plateforme est requis.'], 400);
        }
        $this->ts->tagPlateforme($plateformeId, $tagIds);
        return $this->json(['message' => 'Tags modifiés avec succès.'], 200);
    }

    #[Route('/utilisateur/tags', methods: ['POST'])]
    public function postUtilisateurTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $utilisateurId = $json['id'] ?? null;
        $tagId = $json['tagId'] ?? null;
        if ($utilisateurId === null) {
            return $this->json(['message' => 'L\'ID de l\'utilisateur est requis.'], 400);
        }
        if ($tagId === null) {
            return $this->json(['message' => 'L\'ID du tag est requis.'], 400);
        }
        $this->ts->tagUtilisateur($utilisateurId, $tagId);
        return $this->json(['message' => 'Tag ajouté avec succès.'], 200);
    }

    #[Route('/utilisateur/tags', methods: ['DELETE'])]
    public function deleteUtilisateurTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $utilisateurId = $json['id'] ?? null;
        $tagId = $json['tagId'] ?? null;
        if ($utilisateurId === null) {
            return $this->json(['message' => 'L\'ID de l\'utilisateur est requis.'], 400);
        }
        if ($tagId === null) {
            return $this->json(['message' => 'L\'ID du tag est requis.'], 400);
        }
        $this->ts->untagUtilisateur($utilisateurId, $tagId);
        return $this->json(['message' => 'Tag supprimé avec succès.'], 200);
    }
}
