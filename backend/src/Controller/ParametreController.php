<?php

namespace App\Controller;

use App\Service\ParametreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ParametreController extends AbstractController
{
    public function __construct(
        private readonly ParametreService $parametreService,
        private readonly TranslatorInterface $translator
    ) {}

    #[Route('/parametres/application', methods: ['GET'])]
    public function getApplicationSettings(): Response
    {
        $response = $this->json($this->parametreService->getApplicationSettings());
        $response->setPublic();
        $response->setMaxAge(300);

        return $response;
    }

    #[Route('/parametres/application', methods: ['POST'])]
    public function updateApplicationSettings(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $name = trim((string) $request->request->get('nom_application', ''));
        $primaryColor = trim((string) $request->request->get('couleur_principale', ''));
        $homeView = (string) $request->request->get('vue_accueil', '');
        if ($name === '' || !preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) || !in_array($homeView, ['carte', 'liste'], true)) {
            throw new BadRequestHttpException($this->translator->trans('parametre.exception.nom_couleur_vue_obligatoire'));
        }

        $logoPath = null;
        $logo = $request->files->get('logo');
        if ($logo !== null) {
            if (!$logo->isValid() || !str_starts_with((string) $logo->getMimeType(), 'image/')) {
                throw new BadRequestHttpException($this->translator->trans('parametre.exception.logo_image_invalide'));
            }

            if ($logo->getSize() > 2 * 1024 * 1024) {
                throw new BadRequestHttpException($this->translator->trans('parametre.exception.logo_trop_volumineux'));
            }

            $content = file_get_contents($logo->getPathname());
            if ($content === false) {
                throw new BadRequestHttpException($this->translator->trans('parametre.exception.logo_non_lisible'));
            }
            $logoPath = 'data:' . $logo->getMimeType() . ';base64,' . base64_encode($content);
        }

        return $this->json($this->parametreService->updateApplicationSettings($name, $primaryColor, $homeView, $logoPath));
    }
}
