<?php

namespace App\Repository;

use App\Entity\PlateformePseudoIdMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlateformePseudoIdMapping>
 */
class PlateformePseudoIdMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlateformePseudoIdMapping::class);
    }

    public function findByPlateformeId(int $plateformeId): ?PlateformePseudoIdMapping
    {
        return $this->findOneBy(['plateformeId' => $plateformeId]);
    }

    public function findByPlateformeIdAndHost(int $plateformeId, ?string $instanceHost = null): ?PlateformePseudoIdMapping
    {
        return $this->findOneBy(['plateformeId' => $plateformeId, 'instanceHost' => $instanceHost]);
    }

    public function findAllMappings(): array
    {
        return $this->findAll();
    }
}

