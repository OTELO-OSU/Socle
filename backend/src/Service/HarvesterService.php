<?php

namespace App\Service;

use App\Entity\Harvester;
use App\Entity\Plateforme;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurMachine;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HarvesterService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly Security $security,
        private readonly SerializerInterface $serializerInterface,
        private readonly PlateformeService $plateformeService,
        private readonly TranslatorInterface $translator
    ) {}

    public function creerHMT(string $clientId): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));
        $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId]) && throw new ConflictHttpException($this->translator->trans('harvester.exception.utilisateur_machine_existe'));

        $clientSecret = bin2hex(random_bytes(16));
        $hashedClientSecret = hash('sha256', $clientSecret);

        $user = new Utilisateur();
        $user->setAdmis(\false);
        $user->setEmailValide(\false);
        $user->setAttenteApprobationDU(\false);
        $user->setDoctorant(\false);
        $user->setRoles(["ROLE_MACHINE"]);

        $userMachine = new UtilisateurMachine();
        $userMachine->setClientId($clientId);
        $userMachine->setClientSecret($hashedClientSecret);
        $userMachine->setUtilisateur($user);
        $this->em->persist($user);
        $this->em->persist($userMachine);
        $this->em->flush();

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $hmt = \base64_encode($clientId) . "." . \base64_encode($clientSecret) . "." . \base64_encode($host);

        return ["hmt" => $hmt];
    }

    public function ajoutHMT(string $hmt): void
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));

        $parts = explode(".", $hmt);
        if (count($parts) !== 3) {
            throw new BadRequestHttpException($this->translator->trans('harvester.exception.hmt_invalide'));
        }
        $clientId = base64_decode($parts[0]);
        $clientSecret = base64_decode($parts[1]);
        $host = base64_decode($parts[2]);

        $this->em->getRepository(Harvester::class)->findOneBy(["host" => $host]) && throw new ConflictHttpException($this->translator->trans('harvester.exception.harvester_existe'));

        $harvester = new Harvester();
        $harvester->setClientId($clientId);
        $harvester->setClientSecret($clientSecret);
        $harvester->setHost($host);
        $this->em->persist($harvester);
        $this->em->flush();
    }

    public function getPlateformeListePourMachine(string $clientId, string $clientSecret): array
    {
        $hashedClientSecret = hash('sha256', $clientSecret);
        $harvester = $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId, "clientSecret" => $hashedClientSecret]) ?? throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_machine_nf'));
        $tagsUserMachine = $harvester->getUtilisateur()->getUtilisateurTags();
        $arrayIds = [];
        foreach ($tagsUserMachine as $tag) {
            $arrayIds[] = $tag->getTagId();
        }
        $plateformesTags = $this->plateformeService->getPlateformeAuxTags($arrayIds);
        $plateformes = [];
        foreach ($plateformesTags as $plateformeTag) {
            $plateformes[] = $plateformeTag->getPlateforme();
        }

        return $plateformes;
    }

    public function getPlateformeDetailPourMachine(string $clientId, string $clientSecret, int $plateformeId): Plateforme
    {
        $hashedClientSecret = hash('sha256', $clientSecret);
        $harvester = $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId, "clientSecret" => $hashedClientSecret]) ?? throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_machine_nf'));
        $plateformeMere = $this->plateformeService->getPlateformeAvecDernierHistorique($plateformeId, true);

        $tagsUserMachine = $harvester->getUtilisateur()->getUtilisateurTags();
        $arrayIds = [];
        foreach ($tagsUserMachine as $tag) {
            $arrayIds[] = $tag->getTagId();
        }

        $plateformeTags = $plateformeMere->getPlateformeTags();
        $arrayTags = [];
        foreach ($plateformeTags as $PlateformeTags) {
            $arrayTags[] = $PlateformeTags->getTagId();
        }

        if (empty(array_intersect($arrayIds, $arrayTags))) {
            throw new AccessDeniedHttpException($this->translator->trans('plateforme.exception.acces_plateforme'));
        } else {
            return $plateformeMere;
        }
    }

    public function getListePlateformeHarveste(): array
    {
        // verifier que le user est connecté
        if (!$this->security->getUser()) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('auth.exception.non_connecte'));
        }

        $harvesters = $this->em->getRepository(Harvester::class)->findAll();

        $plateformesArray = [];

        foreach ($harvesters as $harvester) {
            $host = $harvester->getHost();
            try {
                // Création du header Authorization en Basic Auth avec le clientId et le clientSecret
                $authorizationHeader = 'Basic ' . base64_encode($harvester->getClientId() . ':' . $harvester->getClientSecret());
                $response = $this->httpClient->request('GET', "https://$host/api/hmt/plateformes", [
                    'timeout' => 10,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $authorizationHeader,
                    ],
                ]);
            } catch (TransportExceptionInterface $e) {
                continue;
            }

            if ($response->getStatusCode() >= 400) {
                continue;
            }

            $plateformes = $response->toArray(false);

            if (!is_array($plateformes)) {
                continue;
            }

            foreach ($plateformes as $plateforme) {
                $tagsPlateforme = $plateforme['tags_ids'] ?? [];
                $bool = $this->verifPermission($tagsPlateforme);
                if ($bool) {
                    $plateformesArray[$host][] = $plateforme;
                }
            }
        }

        return $plateformesArray;
    }

    public function getDetailPlateformeHarvester(int $id, string $hosts): array
    {
        // verifier que le user est connecté
        if (!$this->security->getUser()) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('auth.exception.non_connecte'));
        }

        $harvester = $this->em->getRepository(Harvester::class)->findOneBy(['host' => $hosts]);

        if (!$harvester) {
            throw new NotFoundHttpException($this->translator->trans('harvester.exception.harvester_nf'));
        }

        try {
            $authorizationHeader = 'Basic ' . base64_encode($harvester->getClientId() . ':' . $harvester->getClientSecret());
            $response = $this->httpClient->request('GET', "https://{$harvester->getHost()}/api/hmt/plateforme?plateformeId=$id", [
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $authorizationHeader,
                ],
            ]);
        } catch (Exception $e) {
            throw new ServiceUnavailableHttpException(null, "Erreur lors de la récupération des données de la plateforme.");
        }

        $plateforme = $response->toArray(false);

        if (!is_array($plateforme)) {
            throw new ServiceUnavailableHttpException(null, "Erreur lors de la récupération des données de la plateforme.");
        }

        $tagsPlateforme = $plateforme['tags_ids'] ?? [];
        $bool = $this->verifPermission($tagsPlateforme);
        if (!$bool) {
            throw new AccessDeniedHttpException($this->translator->trans('plateforme.exception.acces_plateforme'));
        }

        return $plateforme;
    }

    function supprimerHMT(int $id): void
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));

        $harvester = $this->em->getRepository(UtilisateurMachine::class)->find($id) ?? throw new NotFoundHttpException($this->translator->trans('utilisateur.exception.utilisateur_machine_nf'));
        $userMachine = $harvester->getUtilisateur();
        $this->em->remove($harvester);
        $this->em->remove($userMachine);
        $this->em->flush();
    }

    function supprimerHarvester(int $id): void
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));

        $harvester = $this->em->getRepository(Harvester::class)->find($id) ?? throw new NotFoundHttpException($this->translator->trans('harvester.exception.harvester_nf'));
        $this->em->remove($harvester);
        $this->em->flush();
    }

    function getHarvesters(): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));
        return $this->em->getRepository(Harvester::class)->findAll();
    }

    function getHMT(): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new AccessDeniedHttpException($this->translator->trans('harvester.exception.gerer_harvester'));
        return $this->em->getRepository(UtilisateurMachine::class)->findAll();
    }

    function verifPermission(array $tagsArray)
    {
        /** @var Utilisateur $user */
        $user = $this->security->getUser();
        if (\in_array('ROLE_ADMIN', $user->getRoles())) return true;
        if (\in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
        if (\in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;

        $userTags = $user->getUtilisateurTags()->map(fn($ut) => $ut->getTagId())->toArray();
        foreach ($tagsArray as $tagId) {
            if (\in_array($tagId, $userTags)) {
                return true;
            }
        }
        return false;
    }

    function getPlateformeHarvesteJson(int $plateformeId, string $hosts): string
    {
        // verifier que le user est connecté
        if (!$this->security->getUser()) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('auth.exception.non_connecte'));
        }

        $plt = $this->getDetailPlateformeHarvester($plateformeId, $hosts);
        $json = $this->serializerInterface->serialize($plt, 'json', ['groups' => ['plateforme:detail']]);
        return $json;
    }
}
