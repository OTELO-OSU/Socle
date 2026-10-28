<?php

namespace App\Controller;

use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TagController extends AbstractController
{
    public function __construct(private readonly TagService $ts, private readonly TranslatorInterface $translator) {}

    #[Route('/tags', methods: ['GET'])]
    public function getTags(): Response
    {
        $tags = $this->ts->getTags();

        return $this->json($tags, 200);
    }

    #[Route('/admin/tags', methods: ['GET'])]
    public function getTagsAdmin(): Response
    {
        $tags = $this->ts->getTagsAdmin();

        return $this->json($tags, 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/plateforme/tags', methods: ['GET'])]
    public function getPlateformeTags(Request $request): Response
    {
        $plateformeId = $request->query->get('id', null);
        if ($plateformeId === null) {
            throw new BadRequestHttpException($this->translator->trans('plateforme.exception.plateforme_id_manquant'));
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
            throw new BadRequestHttpException($this->translator->trans('plateforme.exception.plateforme_id_manquant'));
        }
        $this->ts->tagPlateforme($plateformeId, $tagIds);
        return $this->json(['message' => $this->translator->trans('tag.succes.tags_modifies_succes')], 200);
    }

    #[Route('/utilisateur/tags', methods: ['POST'])]
    public function postUtilisateurTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $utilisateurId = $json['id'] ?? null;
        $tagId = $json['tagId'] ?? null;
        if ($utilisateurId === null) {
            throw new BadRequestHttpException($this->translator->trans('utilisateur.exception.utilisateur_id_manquant'));
        }
        if ($tagId === null) {
            throw new BadRequestHttpException($this->translator->trans('tag.exception.tag_id_manquant'));
        }
        $this->ts->tagUtilisateur($utilisateurId, $tagId);
        return $this->json(['message' => $this->translator->trans('tag.succes.tags_modifies_succes')], 200);
    }

    #[Route('/utilisateur/tags', methods: ['DELETE'])]
    public function deleteUtilisateurTags(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $utilisateurId = $json['id'] ?? null;
        $tagId = $json['tagId'] ?? null;
        if ($utilisateurId === null) {
            throw new BadRequestHttpException($this->translator->trans('utilisateur.exception.utilisateur_id_manquant'));
        }
        if ($tagId === null) {
            throw new BadRequestHttpException($this->translator->trans('tag.exception.tag_id_manquant'));
        }
        $this->ts->untagUtilisateur($utilisateurId, $tagId);
        return $this->json(['message' => $this->translator->trans('tag.succes.tags_modifies_succes')], 200);
    }
}
