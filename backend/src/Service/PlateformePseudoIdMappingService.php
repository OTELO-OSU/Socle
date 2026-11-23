<?php

namespace App\Service;

use App\Entity\PlateformePseudoIdMapping;
use App\Repository\PlateformePseudoIdMappingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlateformePseudoIdMappingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PlateformePseudoIdMappingRepository $repository,
        private readonly TranslatorInterface $translator
    ) {}

    public function createOrUpdateMapping(int $plateformeId, string $pseudoId, ?string $instanceHost = null): PlateformePseudoIdMapping
    {
        $mapping = $this->repository->findByPlateformeIdAndHost($plateformeId, $instanceHost);

        if (!$mapping) {
            $mapping = new PlateformePseudoIdMapping();
            $mapping->setPlateformeId($plateformeId);
            $mapping->setInstanceHost($instanceHost);
            $mapping->setCreatedAt(new \DateTimeImmutable());
        }

        $mapping->setPseudoIdMapped($pseudoId);
        $mapping->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($mapping);
        $this->em->flush();

        return $mapping;
    }

    public function getMapping(int $plateformeId, ?string $instanceHost = null): ?PlateformePseudoIdMapping
    {
        return $this->repository->findByPlateformeIdAndHost($plateformeId, $instanceHost);
    }

    public function getMappedPseudoId(int $plateformeId, ?string $instanceHost = null): ?string
    {
        $mapping = $this->repository->findByPlateformeIdAndHost($plateformeId, $instanceHost);
        return $mapping?->getPseudoIdMapped();
    }

    public function deleteMapping(int $plateformeId, ?string $instanceHost = null): void
    {
        $mapping = $this->repository->findByPlateformeIdAndHost($plateformeId, $instanceHost);
        if ($mapping) {
            $this->em->remove($mapping);
            $this->em->flush();
        }
    }

    public function getAllMappings(): array
    {
        return $this->repository->findAllMappings();
    }
}
