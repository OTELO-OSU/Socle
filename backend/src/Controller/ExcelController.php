<?php

namespace App\Controller;

use App\Service\ExcelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}
