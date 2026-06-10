<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeTags;
use App\Security\Voter\PlateformeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TagService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $params,
        private readonly Security $security
    ) {}

    public function getTags(): array
    {
        $centralUrl = $this->params->get('central_tag');

        try {
            // Requete HTTP vers le service central pour recuperer les tags.
            $response = $this->httpClient->request('GET', $centralUrl, [
                'timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new Exception("Impossible de joindre le service central des tags ($centralUrl): " . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw new Exception("Le service central des tags a retourne une erreur HTTP " . $response->getStatusCode() . ".");
        }

        $tags = $response->toArray(false);

        if (!is_array($tags)) {
            throw new Exception("La réponse de l'URL centrale n'est pas un JSON valide.");
        }

        return $tags;
    }

    public function getPlateformeTags(int $plateformeId): array
    {
        /** @var Plateforme $plateforme */
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId) ?? throw new Exception("Plateforme non trouvée.", 404);

        $this->security->isGranted(PlateformeVoter::TAG, $plateforme) || throw new Exception("Accès refusé.", 403);

        $plateformeTags = $plateforme->getPlateformeTags();
        $arrayTags = [];
        foreach ($plateformeTags as $PlateformeTags) {
            $arrayTags[] = $PlateformeTags->getTagId();
        }

        $centralUrl = $this->params->get('central_tag');

        try {
            // Requete HTTP vers le service central pour recuperer les tags.
            $response = $this->httpClient->request('GET', $centralUrl, [
                'timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new Exception("Impossible de joindre le service central des tags ($centralUrl): " . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw new Exception("Le service central des tags a retourne une erreur HTTP " . $response->getStatusCode() . ".");
        }

        $tags = $response->toArray(false);

        if (!is_array($tags)) {
            throw new Exception("La réponse de l'URL centrale n'est pas un JSON valide.");
        }

        $tags = array_values(array_filter($tags, function ($tag) use ($arrayTags) {
            return in_array($tag['id'], $arrayTags, true);
        }));

        return $tags;
    }

    public function tagPlateforme(int $plateformeId, array $tagIds): void
    {
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("Plateforme non trouvée.");
        }

        $currentTags = $plateforme->getPlateformeTags();
        foreach ($currentTags as $currentTag) {
            if (!in_array($currentTag->getTagId(), $tagIds)) {
                $this->em->remove($currentTag);
            }
        }

        foreach ($tagIds as $tagId) {
            $existingTag = $this->em->getRepository(PlateformeTags::class)->findOneBy([
                'plateforme' => $plateforme,
                'tagId' => $tagId,
            ]);

            if ($existingTag) {
                // Tag déjà présent, pas besoin de faire quoi que ce soit
                continue;
            }

            $PlateformeTags = new PlateformeTags($plateforme, $tagId);
            $this->em->persist($PlateformeTags);
        }
        $this->em->flush();
    }

    public function untagPlateforme(int $plateformeId, int $tagId): void
    {
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId);
        if (!$plateforme) {
            throw new Exception("Plateforme non trouvée.");
        }

        $existingTag = $this->em->getRepository(PlateformeTags::class)->findOneBy([
            'plateforme' => $plateforme,
            'tagId' => $tagId,
        ]);

        if ($existingTag) {
            $this->em->remove($existingTag);
            $this->em->flush();
        }
    }
}
