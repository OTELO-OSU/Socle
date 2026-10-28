<?php

namespace App\Controller;

use App\Service\UniteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UniteController extends AbstractController
{
    public function __construct(private readonly UniteService $us, private readonly TranslatorInterface $translator) {}

    #[Route('/unite', methods: ['GET'])]
    public function getUnite(): Response
    {
        $unites = $this->us->getUnites();
        return $this->json($unites, 200, [], ['groups' => ['unite:list', 'unite:detail', 'utilisateur:list']]);
    }

    #[Route('/unite/directeur', methods: ['PATCH'])]
    public function patchUnite(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!\is_array($data) || !\array_key_exists('unite_id', $data) || !\array_key_exists('utilisateur_id', $data)) {
            throw new BadRequestHttpException($this->translator->trans('exception.bad_request.champs_manquants'));
        }
        $this->us->modifierDirecteurUnite($data['unite_id'], $data['utilisateur_id']);
        return $this->json(['message' => $this->translator->trans('unite.succes.du_modifie')], 200);
    }

    #[Route('/unite/directeur/delegue', methods: ['PATCH'])]
    public function switchDelegueDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $operation = $json['operation'] ?? null;
        if (!in_array($operation, ['add', 'remove'])) {
            throw new BadRequestHttpException($this->translator->trans('exception.bad_request.operation_invalide'));
        }

        $uniteId = $json['unite_id'] ?? null;
        if (!$uniteId) {
            throw new BadRequestHttpException($this->translator->trans('unite.exception.unite_id_manquant'));
        }

        $message = null;

        $id = $json['delegue_directeur_unite_id'] ?? null;

        if (!$id) {
            throw new BadRequestHttpException($this->translator->trans('utilisateur.exception.utilisateur_id_manquant'));
        }

        if ($operation === 'add') {
            $this->us->ajouterDelegue($id, $uniteId);
            $message = $this->translator->trans('unite.succes.delegue_unite_ajoute');
        } else {
            $this->us->supprimerDelegue($id, $uniteId);
            $message = $this->translator->trans('unite.succes.delegue_unite_retire');
        }

        return $this->json(['message' => $message], 200);
    }
}
