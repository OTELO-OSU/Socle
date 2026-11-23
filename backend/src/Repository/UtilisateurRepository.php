<?php

namespace App\Repository;

use App\Entity\Plateforme;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findByRole(string $role): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $ids = $conn->executeQuery(
            'SELECT id FROM utilisateur WHERE roles::jsonb @> :role',
            ['role' => json_encode([$role])]
        )->fetchFirstColumn();

        if ($ids === []) {
            return [];
        }

        return $this->findBy(['id' => $ids]);
    }

    public function findUserAyantAccesPlateforme(Plateforme $plateforme): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $plateformeHisto = $plateforme->getLastHistorique();

        $ids = $conn->executeQuery(
            'SELECT DISTINCT u.id
FROM utilisateur u
LEFT JOIN plateforme_historique_personnel php
    ON u.id = php.utilisateur_id
    AND php.plateforme_historique_id = :plateforme_historique_id
LEFT JOIN entite e
    ON e.directeur_id = u.id
    AND e.id = (
        SELECT ph.laboratoire_operateur_id
        FROM plateforme_historique ph
        WHERE ph.id = :plateforme_historique_id
    )
WHERE (
    u.roles::jsonb @> \'["ROLE_ADMIN"]\'::jsonb OR
    (
        php.id IS NOT NULL AND (
            u.roles::jsonb @> \'["ROLE_DIR_OSU"]\'::jsonb OR
            u.roles::jsonb @> \'["ROLE_DIR_OSU_DELEGUE"]\'::jsonb OR
            php.responsable_scientifique = true OR
            php.responsable_technique = true OR
            php.responsable_plateforme = true
        )
    ) OR
    e.id IS NOT NULL
)',
            ['plateforme_historique_id' => $plateformeHisto->getId()]
        )->fetchFirstColumn();

        if ($ids === []) {
            return [];
        }

        return $this->findBy(['id' => $ids]);
    }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
