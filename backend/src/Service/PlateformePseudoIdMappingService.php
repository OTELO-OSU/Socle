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

    public function createOrUpdateMapping(int $plateformeId, string $pseudoId): PlateformePseudoIdMapping
    {
        $mapping = $this->repository->findByPlateformeId($plateformeId);

        if (!$mapping) {
            $mapping = new PlateformePseudoIdMapping();
            $mapping->setPlateformeId($plateformeId);
            $mapping->setCreatedAt(new \DateTimeImmutable());
        }

        $mapping->setPseudoIdMapped($pseudoId);
        $mapping->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($mapping);
        $this->em->flush();

        return $mapping;
    }

    public function getMapping(int $plateformeId): ?PlateformePseudoIdMapping
    {
        return $this->repository->findByPlateformeId($plateformeId);
    }

    public function getMappedPseudoId(int $plateformeId): ?string
    {
        $mapping = $this->repository->findByPlateformeId($plateformeId);
        return $mapping?->getPseudoIdMapped();
    }

    public function deleteMapping(int $plateformeId): void
    {
        $mapping = $this->repository->findByPlateformeId($plateformeId);
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
