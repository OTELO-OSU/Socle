<?php

namespace App\Controller;

use App\Service\ExcelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ExcelController extends AbstractController
{
    public function __construct(private readonly ExcelService $es) {}

    #[Route('/export/plateforme', methods: ['GET'])]
    public function exportPlateforme(Request $request): Response
    {
        $id = $request->query->get('id');
        $content = $this->es->exportSinglePlatformToExcel($id);

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plateforme_' . $id . '.xlsx"',
        ]);
    }

    #[Route('/export/plateformes', methods: ['GET'])]
    public function exportToutesLesPlateformes(): Response
    {
        $content = $this->es->exportAllPlatformsToExcel();

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plateformes.xlsx"',
        ]);
    }

    #[Route('/export/plateformes/tag', methods: ['GET'])]
    public function exportPlateformesParTag(Request $request): Response
    {
        $tagId = $request->query->getInt('tagId');
        if ($tagId <= 0 || !$request->query->has('tagId')) {
            throw new BadRequestHttpException('Un identifiant de tag positif est obligatoire.');
        }

        $content = $this->es->exportPlatformsByTagToExcel($tagId);

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plateformes_tag_' . $tagId . '.xlsx"',
        ]);
    }

    #[Route('/export/plateformes/unite', methods: ['GET'])]
    public function exportPlateformesParUnite(Request $request): Response
    {
        $laboId = trim((string) $request->query->get('laboId', ''));
        if ($laboId === '') {
            throw new BadRequestHttpException('Un identifiant laboratoire est obligatoire.');
        }

        $content = $this->es->exportPlatformsByUnitRorToExcel($laboId);

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plateformes_unite_' . rawurlencode($laboId) . '.xlsx"',
        ]);
    }
}
