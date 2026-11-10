<?php

namespace App\Controller;

use App\Service\VersionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class VersionController extends AbstractController
{
    public function __construct() {}

    #[Route('/version', methods: ['GET'])]
    public function version(VersionService $versionService): JsonResponse
    {
        return $this->json($versionService->getVersionInfo());
    }
}
