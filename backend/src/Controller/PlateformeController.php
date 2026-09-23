<?php

namespace App\Controller;

use App\DTO\PlateformeHistoriqueDTO;
use App\Service\PlateformeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PlateformeController extends AbstractController
{
    public function __construct(private readonly PlateformeService $plateformeService, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator) {}

    #[Route('/plateforme', methods: ['GET'])]
    public function getPlateforme(Request $request): Response
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $search = $request->query->get('search', null);
        $plateformes = $this->plateformeService->getPlateformeParPage($page, $search);
        return $this->json($plateformes, 200, [], ['groups' => ['plateforme:list']]);
    }

    #[Route('/plateforme/publique', methods: ['GET'])]
    public function getPlateformePublique(): Response
    {
        $plateformes = $this->plateformeService->getPlateformePublique();
        return $this->json($plateformes, 200, [], ['groups' => ['plateforme:list:publique']]);
    }

    #[Route('/plateforme/{id}', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function supprPlateforme(int $id): Response
    {
        $this->plateformeService->supprimerPlateforme($id);

        return $this->json(
            ['message' => 'Plateforme supprimée avec succès.'],
            200,
        );
    }

    #[Route('/plateforme/{id}', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function getPlateformeDetail(int $id): Response
    {
        $plateforme = $this->plateformeService->getPlateformeAvecDernierHistorique($id);

        return $this->json(
            $plateforme,
            200,
            [],
            [
                'groups' => ['plateforme:detail', 'instrument:mutualisation']
            ]
        );
    }

    #[Route('/plateforme', methods: ['POST'])]
    public function creerPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['nom'])) return $this->json(['message' => 'Le nom officiel de la plateforme est requis.'], 400);

        $plt = $this->plateformeService->creerPlateforme($json['nom']);
        return $this->json(['message' => 'Plateforme créée avec succès.', 'plateforme_id' => $plt->getId()], 201);
    }

    #[Route('/plateforme', methods: ['PUT'])]
    public function sauvegarderPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $brouillon = $json['brouillon'] ?? null;
        if ($brouillon === null) return $this->json(['message' => 'Le statut de l\'enregistrement doit être spécifié'], 400);
        unset($json['brouillon']);

        $dto = $this->serializer->deserialize(json_encode($json), PlateformeHistoriqueDTO::class, 'json');

        $errors = [];

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

    #[Route('/plateforme/test', methods: ['POST'])]
    public function testPlateforme(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);

        $dto = $this->serializer->deserialize(json_encode($json), PlateformeHistoriqueDTO::class, 'json');

        $errors = [];

        // Plateforme enregistrée pour validation. Vérification du groupe validation
        $errors = $this->validator->validate($dto, null, ['Default', 'validation']);

        if (count($errors) === 0) {
            $errors = $this->validator->validate($dto, null, ['post_validation']);
        }

        $errorMessages = [];

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['messageArray' => $errorMessages], 400);
        }

        return $this->json(['message' => 'La plateforme est prête à être validée.'], 200);
    }
}
