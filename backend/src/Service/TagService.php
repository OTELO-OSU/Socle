<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeTags;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurTags;
use App\Security\Voter\PlateformeVoter;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
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
            $response = $this->httpClient->request('GET', $centralUrl, [
                'timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                throw new ServiceUnavailableHttpException(
                    null,
                    "Le service central des tags a retourné une erreur HTTP $statusCode."
                );
            }

            $tags = $response->toArray(false);
        } catch (TransportExceptionInterface $e) {
            throw new ServiceUnavailableHttpException(
                null,
                "La connexion au service central des tags a échoué.",
            );
        }

        if (!is_array($tags)) {
            throw new ServiceUnavailableHttpException(
                null,
                "La réponse de l'URL centrale n'est pas un JSON valide."
            );
        }

        return $tags;
    }

    public function getTagsAdmin(): array
    {
        $this->security->isGranted(UtilisateurVoter::TAG_UTILISATEUR, Utilisateur::class) || throw new AccessDeniedHttpException("Accès refusé.");
        $tags = $this->getTags();

        $tagsAvecAffiliationArray = [];
        $tagsAvecAffiliationPlateforme = $this->em->getRepository(PlateformeTags::class)->findAll();
        $tagsAvecAffiliationUser = $this->em->getRepository(UtilisateurTags::class)->findAll();
        foreach ($tagsAvecAffiliationUser as $tagAvecAffiliation) {
            if (array_key_exists($tagAvecAffiliation->getTagId(), $tagsAvecAffiliationArray)) {
                $tagsAvecAffiliationArray[$tagAvecAffiliation->getTagId()]["utilisateurs"][] = $tagAvecAffiliation->getUtilisateur();
            } else {
                $resoudreTag = array_values(array_filter($tags, fn($tag) => $tag['id'] === $tagAvecAffiliation->getTagId()));
                if (count($resoudreTag) === 0) {
                    continue;
                }
                $tagsAvecAffiliationArray[$tagAvecAffiliation->getTagId()] = ["id" => $tagAvecAffiliation->getTagId(), "nom" => $resoudreTag[0]['nom'], "utilisateurs" => [$tagAvecAffiliation->getUtilisateur()]];
            }
        }

        foreach ($tagsAvecAffiliationPlateforme as $tagAvecAffiliation) {
            if (!array_key_exists($tagAvecAffiliation->getTagId(), $tagsAvecAffiliationArray)) {
                $resoudreTag = array_values(array_filter($tags, fn($tag) => $tag['id'] === $tagAvecAffiliation->getTagId()));
                if (count($resoudreTag) === 0) {
                    continue;
                }
                $tagsAvecAffiliationArray[$tagAvecAffiliation->getTagId()] = ["id" => $tagAvecAffiliation->getTagId(), "nom" => $resoudreTag[0]['nom'], "utilisateurs" => []];
            }
        }

        $finalArray = [];
        foreach ($tagsAvecAffiliationArray as $tag) {
            $finalArray[] = $tag;
        }

        return $finalArray;
    }

    public function getPlateformeTags(int $plateformeId): array
    {
        /** @var Plateforme $plateforme */
        $plateforme = $this->em->getRepository(Plateforme::class)->find($plateformeId) ?? throw new NotFoundHttpException("La plateforme n'existe pas.");

        $this->security->isGranted(PlateformeVoter::TAG, $plateforme) || throw new AccessDeniedHttpException("Vous n'avez pas les droits pour consulter les tags de cette plateforme.");

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
            throw new ServiceUnavailableHttpException("Impossible de joindre le service central des tags ($centralUrl): " . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw new ServiceUnavailableHttpException("Le service central des tags a retourne une erreur HTTP " . $response->getStatusCode() . ".");
        }

        $tags = $response->toArray(false);

        if (!is_array($tags)) {
            throw new ServiceUnavailableHttpException("La réponse de l'URL centrale n'est pas un JSON valide.");
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
            throw new NotFoundHttpException("La plateforme n'existe pas.");
        }

        $this->security->isGranted(PlateformeVoter::TAG, $plateforme) || throw new AccessDeniedHttpException("Vous n'avez pas les droits pour tagger cette plateforme");

        $currentTags = $plateforme->getPlateformeTags();
        foreach ($currentTags as $currentTag) {
            if (!in_array($currentTag->getTagId(), $tagIds)) {
                $this->em->remove($currentTag);
            }
        }

        $tagsCentral = $this->getTags();
        $validTagIds = array_map(fn($tag) => $tag['id'], $tagsCentral);

        foreach ($tagIds as $tagId) {
            if (!in_array($tagId, $validTagIds)) {
                throw new BadRequestHttpException("Tag avec l'ID $tagId est invalide.");
            }
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

    public function tagUtilisateur(int $userId, int $tagId): void
    {
        $this->security->isGranted(UtilisateurVoter::TAG_UTILISATEUR, Utilisateur::class) || throw new AccessDeniedHttpException("Vous n'avez pas les droits pour tagger cet utilisateur.");
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($userId);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas.");
        }

        $tagsCentral = $this->getTags();
        $validTagIds = array_map(fn($tag) => $tag['id'], $tagsCentral);

        if (!in_array($tagId, $validTagIds)) {
            throw new BadRequestHttpException("Tag avec l'ID $tagId est invalide.");
        }

        $utilisateur->getUtilisateurTags()->filter(fn(UtilisateurTags $ut) => $ut->getTagId() === $tagId)->first() && throw new ConflictHttpException("L'utilisateur a déjà ce tag.");

        $userTag = new UtilisateurTags($utilisateur, $tagId);
        $this->em->persist($userTag);
        $this->em->flush();
    }

    public function untagUtilisateur(int $userId, int $tagId): void
    {
        $this->security->isGranted(UtilisateurVoter::TAG_UTILISATEUR, Utilisateur::class) || throw new AccessDeniedHttpException("Vous n'avez pas les droits pour enlever le tag de cet utilisateur.");
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->em->getRepository(Utilisateur::class)->find($userId);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur n'existe pas.");
        }

        $tag = $this->em->getRepository(UtilisateurTags::class)->findOneBy([
            'utilisateur' => $utilisateur,
            'tagId' => $tagId,
        ]) or throw new ConflictHttpException("L'utilisateur n'a pas ce tag.");

        $this->em->remove($tag);
        $this->em->flush();
    }
}
