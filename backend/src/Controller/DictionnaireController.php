<?php

namespace App\Controller;

use App\Service\DictionnaireService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DictionnaireController extends AbstractController
{
    public function __construct(private readonly DictionnaireService $ds) {}

    #[Route('/dictionnaire/unites', methods: ['GET'])]
    public function getUnites(): Response
    {
        $unites = $this->ds->getUnite();

        return $this->json($unites, 200, [], ['groups' => 'unite:list']);
    }
}
