<?php

namespace App\Controller;

use App\Service\AgentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AgentController extends AbstractController
{
    public function __construct(private readonly AgentService $utilisateurService, private readonly TranslatorInterface $translator) {}

    #[Route('/agent', methods: ['GET'])]
    public function getUtilisateurs(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $search = $request->query->get('search', null);
        $utilisateurs = $this->utilisateurService->getAgentParPage($page, $search);
        return $this->json($utilisateurs, 200, [], ['groups' => 'utilisateur:list']);
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
        if (!isset($data['email']) || !isset($data['nom']) || !isset($data['prenom']) || !isset($data['employeur_id']) || !isset($data["type_contrat_id"]) || !isset($data["unite_id"])) {
            return $this->json(['message' => $this->translator->trans('exception.bad_request.champs_manquants')], 400);
        }

        $corps = null;
        $grade = null;
        $universiteRattachement = null;
        $doctorant = false;

        if (isset($data['corps_id'])) {
            $corps = $data['corps_id'];
        }

        if (isset($data['grade_id'])) {
            $grade = $data['grade_id'];
        }

        if (isset($data['universite_rattachement_id'])) {
            $universiteRattachement = $data['universite_rattachement_id'];
        }

        if (isset($data['doctorant'])) {
            $doctorant = $data['doctorant'];
        }

        if (isset($data['baps_ids'])) {
            $bapsIds = $data['baps_ids'];
        }

        if (isset($data['emploi_types_ids'])) {
            $emploiTypesIds = $data['emploi_types_ids'];
        }

        if (isset($data['cnu'])) {
            $cnu = $data['cnu'];
        }

        if (isset($data['section'])) {
            $section = $data['section'];
        }

        $utilisateur = $this->utilisateurService->creationUtilisateurParResponsable(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['employeur_id'],
            $data['unite_id'],
            $corps,
            $grade,
            $data['type_contrat_id'],
            $doctorant,
            $universiteRattachement,
            $bapsIds ?? null,
            $emploiTypesIds ?? null,
            $cnu ?? null,
            $section ?? null
        );
        return $this->json(['message' => 'Soumission envoyée avec succès', 'utilisateur' => $utilisateur], 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/agent', methods: ['PATCH'])]
    public function gererResponseDU(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['utilisateurId']) || !isset($data['approbation'])) {
            return $this->json(['message' => $this->translator->trans('exception.bad_request.champs_manquants')], 400);
        }

        if (!is_bool($data['approbation'])) {
            return $this->json(['message' => $this->translator->trans('exception.bad_request.champs_approbation_boolean')], 400);
        }

        $approbation = $data['approbation'];

        if ($approbation) {
            $this->utilisateurService->approuveUtilisateurEnAttenteDeValidationDuDu($data['utilisateurId']);
            return $this->json(['message' => $this->translator->trans('admin.succes.utilisateur_approuve')], 200);
        } else {
            $this->utilisateurService->refusUtilisateurEnAttenteDeValidationDuDu($data['utilisateurId']);
            return $this->json(['message' => $this->translator->trans('admin.succes.utilisateur_refuse')], 200);
        }
    }

    #[Route('/agent', methods: ['PUT'])]
    public function modifierAgent(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['utilisateur_id']) || !isset($data['email']) || !isset($data['nom']) || !isset($data['prenom']) || !isset($data['employeur_id']) || !isset($data['type_contrat_id']) || !isset($data['unite_id'])) {
            return $this->json(['message' => $this->translator->trans('exception.bad_request.champs_manquants')], 400);
        }

        $corps = null;
        $grade = null;
        $universiteRattachement = null;
        $doctorant = false;
        $bapsIds = [];
        $emploiTypesIds = [];
        $cnu = null;
        $section = null;
        $doctorant = false;

        if (isset($data['corps_id'])) {
            $corps = $data['corps_id'];
        }

        if (isset($data['grade_id'])) {
            $grade = $data['grade_id'];
        }

        if (isset($data['universite_rattachement_id'])) {
            $universiteRattachement = $data['universite_rattachement_id'];
        }

        if (isset($data['doctorant'])) {
            $doctorant = $data['doctorant'];
        }

        if (isset($data['baps_ids'])) {
            $bapsIds = $data['baps_ids'];
        }

        if (isset($data['emploi_types_ids'])) {
            $emploiTypesIds = $data['emploi_types_ids'];
        }

        if (isset($data['cnu'])) {
            $cnu = $data['cnu'];
        }

        if (isset($data['section'])) {
            $section = $data['section'];
        }

        $utilisateur = $this->utilisateurService->modificationUtilisateur(
            $data['utilisateur_id'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['employeur_id'],
            $data['unite_id'],
            $corps,
            $grade,
            $data['type_contrat_id'],
            $doctorant,
            $universiteRattachement,
            $bapsIds,
            $emploiTypesIds,
            $cnu,
            $section
        );

        return $this->json(['message' => $this->translator->trans('admin.succes.utilisateur_modifie'), 'utilisateur' => $utilisateur], 200, [], ['groups' => 'utilisateur:list']);
    }
}
