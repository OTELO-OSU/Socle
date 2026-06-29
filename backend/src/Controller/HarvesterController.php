<?php

namespace App\Controller;

use App\Service\HarvesterService;
use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HarvesterController extends AbstractController
{
    public function __construct(private readonly HarvesterService $hs) {}

    #[Route('/harvester/hmt', methods: ['GET'])]
    public function gethmt(Request $request): Response
    {
        $clientId = $request->query->get('clientId', null);
        if ($clientId === null) {
            return $this->json(['message' => 'Le clientId est requis.'], 400);
        }
        $hmt = $this->hs->creerHMT($clientId);

        return $this->json($hmt, 200);
    }

    #[Route('/harvester/hmt', methods: ['DELETE'])]
    public function deleteHMT(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $clientId = $json['clientId'] ?? null;
        if ($clientId === null) {
            return $this->json(['message' => 'Le clientId est requis.'], 400);
        }

        $this->hs->supprimerHMT($clientId);

        return $this->json(['message' => 'HMT supprimé avec succès.'], 200);
    }

    #[Route('/harvester', methods: ['DELETE'])]
    public function deleteHarvester(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $harvesterId = $json['harvesterId'] ?? null;
        if ($harvesterId === null) {
            return $this->json(['message' => 'Le harvesterId est requis.'], 400);
        }

        $this->hs->supprimerHarvester($harvesterId);

        return $this->json(['message' => 'Harvester supprimé avec succès.'], 200);
    }

    #[Route('/harvester/hmt', methods: ['POST'])]
    public function postHMT(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $hmt = $json['hmt'] ?? null;
        if ($hmt === null) {
            return $this->json(['message' => 'Le hmt est requis.'], 400);
        }
        $this->hs->ajoutHMT($hmt);
        return $this->json(['message' => 'HMT ajouté avec succès.'], 200);
    }

    #[Route('/harvester/plateformes', methods: ['GET'])]
    public function getListePlateformeHarvester(Request $request): Response
    {
        $plateformes = $this->hs->getListePlateformeHarveste();

        return $this->json($plateformes, 200);
    }

    #[Route('/harvester/plateforme', methods: ['GET'])]
    public function getDetailPlateformeHarvester(Request $request): Response
    {
        $idPlateforme = $request->query->get('id', null);
        $host = $request->query->get('host', null);
        $plateformes = $this->hs->getDetailPlateformeHarvester($idPlateforme, $host);

        return $this->json($plateformes, 200);
    }

    #[Route('/hmt/plateformes', methods: ['GET'])]
    public function getListePlateformeHarvesterHMT(Request $request): Response
    {
        $clientId = $request->query->get('clientId', null);
        $clientSecret = $request->query->get('clientSecret', null);
        if ($clientId === null || $clientSecret === null) {
            return $this->json(['message' => 'Le clientId et le clientSecret sont requis.'], 400);
        }
        $plateformes = $this->hs->getPlateformeListePourMachine($clientId, $clientSecret);

        return $this->json($plateformes, 200, [], ['groups' => ['plateforme:list']]);
    }

    #[Route('/hmt/plateforme', methods: ['GET'])]
    public function getDetailPlateformeHarvesterHMT(Request $request): Response
    {
        $plateformeId = $request->query->get('id', null);
        $clientId = $request->query->get('clientId', null);
        $clientSecret = $request->query->get('clientSecret', null);
        if ($plateformeId === null || $clientId === null || $clientSecret === null) {
            return $this->json(['message' => 'L\'ID de la plateforme, le clientId et le clientSecret sont requis.'], 400);
        }
        $plateforme = $this->hs->getPlateformeDetailPourMachine($clientId, $clientSecret, (int) $plateformeId);

        return $this->json($plateforme, 200);
    }

    #[Route('/admin/harvesters', methods: ['GET'])]
    public function getAdminHarvestersHMT(): Response
    {
        $harvesters = $this->hs->getHarvesters();
        $hmt = $this->hs->getHMT();
        return $this->json(['harvesters' => $harvesters, 'hmt' => $hmt], 200, [], ['groups' => ['harvester:admin', 'hmt:admin']]);
    }
}
