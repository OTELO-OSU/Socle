<?php

namespace App\Controller;

use App\Service\AgentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AgentController extends AbstractController
{
    public function __construct(private readonly AgentService $utilisateurService) {}

    #[Route('/agent', methods: ['GET'])]
    public function getUtilisateurs(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $search = $request->query->get('search', null);
        $utilisateurs = $this->utilisateurService->getAgentParPage($page, $search);
        return $this->json($utilisateurs, 200, []);
    }
}
