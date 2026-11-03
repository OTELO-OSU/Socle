<?php

namespace App\Controller;

use App\Service\ParametreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ParametreController extends AbstractController
{
    public function __construct(private readonly ParametreService $parametreService) {}

    #[Route('/parametres/application', methods: ['GET'])]
    public function getApplicationSettings(): Response
    {
        return $this->json($this->parametreService->getApplicationSettings());
    }

    #[Route('/parametres/application', methods: ['POST'])]
    public function updateApplicationSettings(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $name = trim((string) $request->request->get('nom_application', ''));
        $primaryColor = trim((string) $request->request->get('couleur_principale', ''));
        $homeView = (string) $request->request->get('vue_accueil', '');
        if ($name === '' || !preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) || !in_array($homeView, ['carte', 'liste'], true)) {
            throw new BadRequestHttpException('Le nom, une couleur hexadécimale valide et une vue d’accueil valide sont obligatoires.');
        }

        $logoPath = null;
        $logo = $request->files->get('logo');
        if ($logo !== null) {
            if (!$logo->isValid() || !str_starts_with((string) $logo->getMimeType(), 'image/')) {
                throw new BadRequestHttpException('Le logo doit être une image valide.');
            }

            if ($logo->getSize() > 2 * 1024 * 1024) {
                throw new BadRequestHttpException('Le logo ne doit pas dépasser 2 Mo.');
            }

            $content = file_get_contents($logo->getPathname());
            if ($content === false) {
                throw new BadRequestHttpException('Le logo ne peut pas être lu.');
            }
            $logoPath = 'data:' . $logo->getMimeType() . ';base64,' . base64_encode($content);
        }

        return $this->json($this->parametreService->updateApplicationSettings($name, $primaryColor, $homeView, $logoPath));
    }
}
