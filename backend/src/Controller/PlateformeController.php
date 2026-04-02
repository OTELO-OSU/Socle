<?php

namespace App\Controller;

use App\DTO\PlateformeHistoriqueDTO;
use App\Service\PlateformeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PlateformeController extends AbstractController
{
    public function __construct(private readonly PlateformeService $plateformeService, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator) {}

    #[Route('/plateforme/creer', methods: ['POST'])]
    public function creerPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['nom'])) return $this->json(['message' => 'Le nom officiel de la plateforme est requis.'], 400);

        $plt = $this->plateformeService->creerPlateforme($json['nom']);
        return $this->json(['message' => 'Plateforme créée avec succès.', 'plateforme_id' => $plt->getLastHistorique()->getId()], 201);
    }

    #[Route('/plateforme/sauvegarder', methods: ['POST'])]
    public function sauvegarderPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $brouillon = $json['brouillon'] ?? null;
        if ($brouillon === null) return $this->json(['message' => 'Le statut de l\'enregistrement doit être spécifié'], 400);

        $dto = $this->serializer->deserialize($request->getContent(), PlateformeHistoriqueDTO::class, 'json');

        if ($brouillon) {
            // Plateforme enregistrée en tant que brouillon, on ne fait rien de plus
            $errors = $this->validator->validate($dto, null, ['Default', 'brouillon']);
        } else {
            // Plateforme enregistrée pour validation. Vérification du groupe validation
            $errors = $this->validator->validate($dto, null, ['Default', 'validation']);

            if (count($errors) === 0) {
                $errors = $this->validator->validate($dto, null, ['post_validation']);
            }
        }

        $errorMessages = [];

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['messageArray' => $errorMessages], 400);
        }

        $this->plateformeService->sauvegarderPlateforme($dto, $brouillon);
        return $this->json(['message' => 'Plateforme sauvegardée avec succès.'], 200);
    }
}
