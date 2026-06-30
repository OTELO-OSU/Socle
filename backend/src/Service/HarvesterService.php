<?php

namespace App\Service;

use App\Entity\Harvester;
use App\Entity\Plateforme;
use App\Entity\PlateformeTags;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurMachine;
use App\Entity\UtilisateurTags;
use App\Security\Voter\PlateformeVoter;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HarvesterService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly Security $security,
        private readonly PlateformeService $plateformeService
    ) {}

    public function creerHMT(string $clientId): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour créer un utilisateur machine.", 403);
        $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId]) && throw new Exception("Un utilisateur machine avec ce clientId existe déjà.", 400);

        $clientSecret = bin2hex(random_bytes(16));

        $user = new Utilisateur();
        $user->setAdmis(\false);
        $user->setEmailValide(\false);
        $user->setAttenteApprobationDU(\false);
        $user->setDoctorant(\false);
        $user->setRoles(["ROLE_MACHINE"]);

        $userMachine = new UtilisateurMachine();
        $userMachine->setClientId($clientId);
        $userMachine->setClientSecret($clientSecret);
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
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour créer un utilisateur machine.", 403);

        $parts = explode(".", $hmt);
        if (count($parts) !== 3) {
            throw new Exception("HMT invalide.", 400);
        }
        $clientId = base64_decode($parts[0]);
        $clientSecret = base64_decode($parts[1]);
        $host = base64_decode($parts[2]);

        $this->em->getRepository(Harvester::class)->findOneBy(["host" => $host]) && throw new Exception("Un harvester avec ce host existe déjà.", 400);

        $harvester = new Harvester();
        $harvester->setClientId($clientId);
        $harvester->setClientSecret($clientSecret);
        $harvester->setHost($host);
        $this->em->persist($harvester);
        $this->em->flush();
    }

    public function getPlateformeListePourMachine(string $clientId, string $clientSecret): array
    {
        $harvester = $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId, "clientSecret" => $clientSecret]) ?? throw new Exception("Harvester non trouvé.", 404);

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

    public function getPlateformeDetailPourMachine(string $clientId, string $clientSecret, int $plateformeId): array
    {
        $harvester = $this->em->getRepository(UtilisateurMachine::class)->findOneBy(["clientId" => $clientId, "clientSecret" => $clientSecret]) ?? throw new Exception("Harvester non trouvé.", 404);
        $plateforme = $this->plateformeService->getPlateformeAvecDernierHistorique($plateformeId, true);

        $tagsUserMachine = $harvester->getUtilisateur()->getUtilisateurTags();
        $arrayIds = [];
        foreach ($tagsUserMachine as $tag) {
            $arrayIds[] = $tag->getTagId();
        }

        $plateformeMereId = $plateforme["id"];
        /** @var Plateforme $plateformeMere */
        $plateformeMere = $this->em->getRepository(Plateforme::class)->find($plateformeMereId);
        $plateformeTags = $plateformeMere->getPlateformeTags();
        $arrayTags = [];
        foreach ($plateformeTags as $PlateformeTags) {
            $arrayTags[] = $PlateformeTags->getTagId();
        }

        if (empty(array_intersect($arrayIds, $arrayTags))) {
            throw new Exception("Accès refusé.", 403);
        } else {
            return $plateforme;
        }
    }

    public function getListePlateformeHarveste(): array
    {
        $harvesters = $this->em->getRepository(Harvester::class)->findAll();

        $plateformesArray = [];

        foreach ($harvesters as $harvester) {
            $host = $harvester->getHost();
            try {
                $response = $this->httpClient->request('GET', "https://$host/api/hmt/plateformes?clientId={$harvester->getClientId()}&clientSecret={$harvester->getClientSecret()}", [
                    'timeout' => 10,
                    'headers' => [
                        'Accept' => 'application/json',
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
        $harvester = $this->em->getRepository(Harvester::class)->findOneBy(['host' => $hosts]);

        if (!$harvester) {
            throw new Exception("Harvester non trouvé.", 404);
        }

        try {
            $response = $this->httpClient->request('GET', "https://{$harvester->getHost()}/api/hmt/plateforme?plateformeId=$id&clientId={$harvester->getClientId()}&clientSecret={$harvester->getClientSecret()}", [
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la requête HTTP.", 500);
        }

        $plateforme = $response->toArray(false);

        if (!is_array($plateforme)) {
            throw new Exception("Erreur lors de la requête HTTP.", 500);
        }

        $tagsPlateforme = $plateforme['tags_ids'] ?? [];
        $bool = $this->verifPermission($tagsPlateforme);
        if (!$bool) {
            throw new Exception("Accès refusé.", 403);
        }

        return $plateforme;
    }

    function supprimerHMT(int $id): void
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour supprimer un utilisateur machine.", 403);

        $harvester = $this->em->getRepository(UtilisateurMachine::class)->find($id) ?? throw new Exception("Harvester non trouvé.", 404);
        $userMachine = $harvester->getUtilisateur();
        $this->em->remove($harvester);
        $this->em->remove($userMachine);
        $this->em->flush();
    }

    function supprimerHarvester(int $id): void
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour supprimer un harvester.", 403);

        $harvester = $this->em->getRepository(Harvester::class)->find($id) ?? throw new Exception("Harvester non trouvé.", 404);
        $this->em->remove($harvester);
        $this->em->flush();
    }

    function getHarvesters(): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour supprimer un harvester.", 403);
        return $this->em->getRepository(Harvester::class)->findAll();
    }

    function getHMT(): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_HMT, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits pour supprimer un harvester.", 403);
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
}
