<?php

namespace App\Controller;

use App\Service\AdminService;
use App\Service\PlateformeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    public function __construct(private readonly AdminService $adminService) {}

    #[Route('/utilisateurs/approuver', methods: ['POST'])]
    public function approuver(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) return $this->json(['message' => 'L\'email est requis.'], 400);

        $this->adminService->approuverUtilisateur($json['email']);
        return $this->json(['message' => 'Utilisateur approuvé avec succès.'], 200);
    }

    #[Route('/utilisateurs/refuser', methods: ['POST'])]
    public function refuser(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) return $this->json(['message' => 'L\'email est requis.'], 400);

        $this->adminService->refuserUtilisateur($json['email']);
        return $this->json(['message' => 'Utilisateur refusé avec succès.'], 200);
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
            return $this->json(['message' => 'L\'ID de la plateforme est requis.'], 400);
        }

        $responsablesScientifiquesIds = $json['responsableScientifiquesIds'] ?? null;
        $responsablesTechniquesIds = $json['responsableTechniquesIds'] ?? null;
        $responsablesPlateformesIds = $json['responsablePlateformeIds'] ?? null;

        $this->adminService->changeResponsable($plateformeId, $responsablesScientifiquesIds, $responsablesTechniquesIds, $responsablesPlateformesIds);
        return $this->json(['message' => 'Responsables de la plateforme mis à jour avec succès.'], 200);
    }
}
