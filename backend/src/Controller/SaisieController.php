<?php

namespace App\Controller;

use App\Service\SaisieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SaisieController extends AbstractController
{
    public function __construct(private readonly SaisieService $ss, private readonly TranslatorInterface $translator) {}

    #[Route('/saisie', methods: ['GET'])]
    public function getOsuAdmin(): Response
    {
        $content = $this->ss->getAnneeDeSaisieEnCours();

        return $this->json(['annee' => $content], 200, [], ['groups' => 'utilisateur:list']);
    }

    #[Route('/saisie', methods: ['PATCH'])]
    public function switchDirOsu(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $annee = $json['annee'] ?? null;

        if (!$annee) {
            throw new BadRequestHttpException($this->translator->trans('annee_saisie.exception.annee_saisie_requise'));
        }

        //Vérifier qu'il y a 4 chiffres
        if (!preg_match('/^\d{4}$/', $annee)) {
            throw new BadRequestHttpException($this->translator->trans('annee_saisie.exception.annee_saisie_invalide'));
        }

        $this->ss->setAnneeDeSaisie($annee);

        return $this->json(['message' => $this->translator->trans('annee_saisie.exception.annee_saisie_change')], 200);
    }
}
