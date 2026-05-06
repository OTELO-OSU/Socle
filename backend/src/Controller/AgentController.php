<?php

namespace App\Controller;

use App\Service\AgentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/agent/en-attente-du-du', methods: ['GET'])]
    public function getUtilisateursEnAttenteDeValidationDuDu(): Response
    {
        $utilisateurs = $this->utilisateurService->getUtilisateurEnAttenteDeValidationDuDu();
        return $this->json($utilisateurs, 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/agent', methods: ['POST'])]
    public function creationUtilisateurParResponsable(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['nom']) || !isset($data['prenom']) || !isset($data['employeur']) || !isset($data["corps"]) || !isset($data["grade"])) {
            return $this->json(['message' => 'Champs requis manquants'], 400);
        }
        $utilisateur = $this->utilisateurService->creationUtilisateurParResponsable(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['employeur'],
            $data['unite'],
            $data['corps'],
            $data['grade']
        );
        return $this->json(['message' => 'Soumission envoyée avec succès', 'utilisateur' => $utilisateur], 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/agent', methods: ['PATCH'])]
    public function gererResponseDU(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['utilisateurId']) || !isset($data['approbation'])) {
            return $this->json(['message' => 'Champs requis manquants'], 400);
        }

        if (!is_bool($data['approbation'])) {
            return $this->json(['message' => 'Le champ approbation doit être un booléen.'], 400);
        }

        $approbation = $data['approbation'];

        if ($approbation) {
            $this->utilisateurService->approuveUtilisateurEnAttenteDeValidationDuDu($data['utilisateurId']);
            return $this->json(['message' => 'L\'utilisateur à bien été approuvé',], 200);
        } else {
            $this->utilisateurService->refusUtilisateurEnAttenteDeValidationDuDu($data['utilisateurId']);
            return $this->json(['message' => 'L\'utilisateur à bien été refusé',], 200);
        }
    }
}
