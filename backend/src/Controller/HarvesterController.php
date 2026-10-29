<?php

namespace App\Controller;

use App\Service\ExcelService;
use App\Service\HarvesterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HarvesterController extends AbstractController
{
    public function __construct(private readonly HarvesterService $hs, private readonly ExcelService $es, private readonly TranslatorInterface $translator) {}

    #[Route('/harvester/hmt', methods: ['GET'])]
    public function gethmt(Request $request): Response
    {
        $clientId = $request->query->get('clientId', null);
        if ($clientId === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.succes.client_id_obligatoire'));
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
            throw new BadRequestHttpException($this->translator->trans('harvester.succes.client_id_obligatoire'));
        }

        $this->hs->supprimerHMT($clientId);

        return $this->json(['message' => $this->translator->trans('harvester.succes.utilisateur_machine_supprime')], 200);
    }

    #[Route('/harvester', methods: ['DELETE'])]
    public function deleteHarvester(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $harvesterId = $json['harvesterId'] ?? null;
        if ($harvesterId === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.succes.harvester_id_obligatoire'));
        }

        $this->hs->supprimerHarvester($harvesterId);

        return $this->json(['message' => $this->translator->trans('harvester.succes.harvester_supprime')], 200);
    }

    #[Route('/harvester/hmt', methods: ['POST'])]
    public function postHMT(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        $hmt = $json['hmt'] ?? null;
        if ($hmt === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.succes.hmt_obligatoire'));
        }
        $this->hs->ajoutHMT($hmt);
        return $this->json(['message' => $this->translator->trans('harvester.succes.hmt_ajoute')], 200);
    }

    #[Route('/harvester/plateformes', methods: ['GET'])]
    public function getListePlateformeHarvester(): Response
    {
        $plateformes = $this->hs->getListePlateformeHarveste();

        return $this->json($plateformes, 200);
    }

    #[Route('/harvester/plateforme', methods: ['GET'])]
    public function getDetailPlateformeHarvester(Request $request): Response
    {
        $idPlateforme = $request->query->get('plateformeId', null);
        $host = $request->query->get('host', null);

        if ($idPlateforme === null || $host === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.exception.plateforme_id_host_obligatoire'));
        }

        $plateformes = $this->hs->getDetailPlateformeHarvester($idPlateforme, $host);

        return $this->json($plateformes, 200);
    }

    #[Route('/hmt/plateformes', methods: ['GET'])]
    public function getListePlateformeHarvesterHMT(Request $request): Response
    {
        // Récupérer le client id et le secret id dans le header authorization en basic auth
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_manquant'));

        if (strpos($authorizationHeader, 'Basic ') !== 0) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_invalide'));

        $base64Credentials = substr($authorizationHeader, 6);
        $decodedCredentials = base64_decode($base64Credentials);

        if (!\str_contains($decodedCredentials, ":")) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_invalide'));

        // Récupération du client id et du secret id
        list($clientId, $clientSecret) = explode(':', $decodedCredentials, 2);

        if ($clientId === null || $clientSecret === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.exception.client_id_secret_id_obligatoire'));
        }
        $plateformes = $this->hs->getPlateformeListePourMachine($clientId, $clientSecret);

        return $this->json($plateformes, 200, [], ['groups' => ['plateforme:list']]);
    }

    #[Route('/hmt/plateforme', methods: ['GET'])]
    public function getDetailPlateformeHarvesterHMT(Request $request): Response
    {
        $plateformeId = $request->query->get('plateformeId', null);

        // Récupérer le client id et le secret id dans le header authorization en basic auth
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_manquant'));

        if (strpos($authorizationHeader, 'Basic ') !== 0) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_invalide'));

        $base64Credentials = substr($authorizationHeader, 6);
        $decodedCredentials = base64_decode($base64Credentials);

        if (!\str_contains($decodedCredentials, ":")) throw new BadRequestHttpException($this->translator->trans('auth.exception.header_authorization_invalide'));

        // Récupération du client id et du secret id
        list($clientId, $clientSecret) = explode(':', $decodedCredentials, 2);

        if ($plateformeId === null || $clientId === null || $clientSecret === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.exception.client_id_secret_id_obligatoire_plateforme_id'));
        }
        $plateforme = $this->hs->getPlateformeDetailPourMachine($clientId, $clientSecret, (int) $plateformeId);

        return $this->json($plateforme, 200, [], ['groups' => ['plateforme:detail']]);
    }

    #[Route('/admin/harvesters', methods: ['GET'])]
    public function getAdminHarvestersHMT(): Response
    {
        $harvesters = $this->hs->getHarvesters();
        $hmt = $this->hs->getHMT();
        return $this->json(['harvesters' => $harvesters, 'hmt' => $hmt], 200, [], ['groups' => ['harvester:admin', 'hmt:admin']]);
    }

    #[Route('/harvester/export/plateforme', methods: ['GET'])]
    public function exportPlateforme(Request $request): Response
    {
        $id = $request->query->get('id');
        $host = $request->query->get('host');

        if ($id === null || $host === null) {
            throw new BadRequestHttpException($this->translator->trans('harvester.exception.plateforme_id_host_obligatoire'));
        }

        $entite = $this->hs->getPlateformeHarvesteJson($id, $host);
        $content = $this->es->exportPlatformeArrayEnExcel([$entite]);

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plateforme_' . $id . '.xlsx"',
        ]);
    }
}
