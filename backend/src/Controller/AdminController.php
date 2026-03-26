<?php

namespace App\Controller;

use App\Service\AdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    public function __construct(private readonly AdminService $adminService) {}

    #[Route('/admettre', methods: ['POST'])]
    public function admettre(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) return $this->json(['message' => 'L\'email est requis.'], 400);

        $this->adminService->admettreUtilisateur($json['email']);
        return $this->json(['message' => 'Utilisateur admis avec succès.'], 200);
    }
}
