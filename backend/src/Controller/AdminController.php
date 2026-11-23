<?php

namespace App\Controller;

use App\Service\AdminService;
use App\Service\PlateformeService;
use App\Service\PlateformePseudoIdMappingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminService $adminService,
        private readonly PlateformeService $plateformeService,
        private readonly PlateformePseudoIdMappingService $pseudoIdMappingService,
        private readonly TranslatorInterface $translator
    ) {}

    #[Route('/utilisateurs/approuver', methods: ['POST'])]
    public function approuver(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) throw new BadRequestHttpException($this->translator->trans('auth.exception.email_obligatoire'));

        $this->adminService->approuverUtilisateur($json['email']);
        return $this->json(['message' => $this->translator->trans('admin.succes.utilisateur_approuve')], 200);
    }

    #[Route('/utilisateurs/refuser', methods: ['POST'])]
    public function refuser(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) throw new BadRequestHttpException($this->translator->trans('auth.exception.email_obligatoire'));

        $this->adminService->refuserUtilisateur($json['email']);
        return $this->json(['message' => $this->translator->trans('admin.succes.utilisateur_refuse')], 200);
    }

    #[Route('/utilisateurs/attente-de-validation', methods: ['GET'])]
    public function getUtilisateurEnAttenteDeValidation(): Response
    {
        $users = $this->adminService->getUtilisateurEnAttenteDeValidation();
        return $this->json($users);
    }

    #[Route('/plateforme/responsables', methods: ['PATCH'])]
    public function patchResponsablePlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);

        $plateformeId = $json['plateformeId'] ?? null;

        if ($plateformeId === null) {
            throw new BadRequestHttpException($this->translator->trans('plateforme.exception.plateforme_id_manquant'));
        }

        $responsablesScientifiquesIds = $json['responsableScientifiquesIds'] ?? null;
        $responsablesTechniquesIds = $json['responsableTechniquesIds'] ?? null;
        $responsablesPlateformesIds = $json['responsablePlateformeIds'] ?? null;

        $this->adminService->changeResponsable($plateformeId, $responsablesScientifiquesIds, $responsablesTechniquesIds, $responsablesPlateformesIds);
        return $this->json(['message' => $this->translator->trans('admin.succes.responsable_plateforme_modifie')], 200);
    }

    #[Route('/admin/plateforme/pseudo-id', methods: ['GET'])]
    public function getAllPseudoIdMappings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $mappings = $this->pseudoIdMappingService->getAllMappings();
        $data = [];
        foreach ($mappings as $mapping) {
            $data[] = [
                'id' => $mapping->getId(),
                'plateformeId' => $mapping->getPlateformeId(),
                'pseudoIdMapped' => $mapping->getPseudoIdMapped(),
                'updatedAt' => $mapping->getUpdatedAt()?->format(\DateTime::ATOM),
            ];
        }
        return $this->json($data);
    }

    #[Route('/admin/plateforme/pseudo-id/{plateformeId}', methods: ['GET'], requirements: ['plateformeId' => '\\d+'])]
    public function getPseudoIdMapping(int $plateformeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $mapping = $this->pseudoIdMappingService->getMapping($plateformeId);
        
        if (!$mapping) {
            return $this->json(['error' => 'Mapping not found'], 404);
        }

        return $this->json([
            'id' => $mapping->getId(),
            'plateformeId' => $mapping->getPlateformeId(),
            'pseudoIdMapped' => $mapping->getPseudoIdMapped(),
            'updatedAt' => $mapping->getUpdatedAt()?->format(\DateTime::ATOM),
        ]);
    }

    #[Route('/admin/plateforme/pseudo-id', methods: ['POST'])]
    public function createOrUpdatePseudoIdMapping(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $json = json_decode($request->getContent(), true);

        $plateformeId = $json['plateformeId'] ?? null;
        $pseudoId = $json['pseudoId'] ?? null;

        if ($plateformeId === null) {
            throw new BadRequestHttpException('plateformeId is required');
        }

        if ($pseudoId === null) {
            throw new BadRequestHttpException('pseudoId is required');
        }

        // For local platforms, modify the pseudo_id directly
        $plateforme = $this->plateformeService->getPlateformeAvecDernierHistorique($plateformeId);
        if ($plateforme) {
            $plateforme->setPseudoId($pseudoId);
        }

        // For remote platforms, create a mapping
        $mapping = $this->pseudoIdMappingService->createOrUpdateMapping($plateformeId, $pseudoId);

        return $this->json([
            'message' => 'Pseudo ID updated successfully',
            'id' => $mapping->getId(),
            'plateformeId' => $mapping->getPlateformeId(),
            'pseudoIdMapped' => $mapping->getPseudoIdMapped(),
            'updatedAt' => $mapping->getUpdatedAt()?->format(\DateTime::ATOM),
        ], 201);
    }

    #[Route('/admin/plateforme/pseudo-id/{plateformeId}', methods: ['DELETE'], requirements: ['plateformeId' => '\\d+'])]
    public function deletePseudoIdMapping(int $plateformeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->pseudoIdMappingService->deleteMapping($plateformeId);
        return $this->json(['message' => 'Mapping deleted successfully']);
    }

    #[Route('/admin/plateformes/select', methods: ['GET'])]
    public function getPlateformesForSelect(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $plateformes = $this->plateformeService->getAllPlateformesForAdmin();
        return $this->json($plateformes);
    }

    #[Route('/admin/plateforme', methods: ['GET'])]
    public function getLocalPlateformes(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $page = (int) ($request->query->get('page') ?? 1);
        $search = $request->query->get('search');
        $tags = $request->query->get('tags') ? array_map('intval', explode(',', $request->query->get('tags'))) : null;
        
        $result = $this->plateformeService->getLocalPlateformeParPage($page, $search, $tags);
        return $this->json($result);
    }
}
