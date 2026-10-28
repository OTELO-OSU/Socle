<?php

namespace App\Controller;

use App\Service\AdminService;
use App\Service\PlateformeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminController extends AbstractController
{
    public function __construct(private readonly AdminService $adminService, private readonly TranslatorInterface $translator) {}

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
}
