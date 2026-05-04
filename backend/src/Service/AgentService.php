<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class AgentService
{

    private const AGENT_PAR_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    function getAgentParPage(int $page, ?string $search): array
    {
        if ($this->security->getUser() === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        $offset = ($page - 1) * self::AGENT_PAR_PAGE;

        $conn = $this->em->getConnection();

        $qb = $conn->createQueryBuilder();
        $qb
            ->select('u.id', 'u.nom, u.prenom, u.email')
            ->from('utilisateur', 'u');

        if ($search) {
            $qb->where('(
                        u.prenom ILIKE :search
                        OR u.nom ILIKE :search
                        OR u.email ILIKE :search
            )');
            $qb->setParameter('search', '%' . $search . '%');
        }

        $qb
            ->orderBy('u.nom', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::AGENT_PAR_PAGE);

        $resQb = $qb->executeQuery()->fetchAllAssociative();

        $countQb = $conn->createQueryBuilder();
        $countQb
            ->select('COUNT(DISTINCT u.id)')
            ->from('utilisateur', 'u');

        if ($search) {
            $countQb->where('(
                u.prenom ILIKE :search
                OR u.nom ILIKE :search
                OR u.email ILIKE :search
            )');
            $countQb->setParameter('search', '%' . $search . '%');
        }

        $total = (int) $countQb->executeQuery()->fetchOne();

        return [
            'agents' => $resQb,
            'page' => $page,
            'pages_total' => ceil($total / self::AGENT_PAR_PAGE),
            'total' => $total
        ];
    }
}
