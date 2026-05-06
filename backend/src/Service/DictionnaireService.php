<?php

namespace App\Service;

use App\Entity\Entite;
use Doctrine\ORM\EntityManagerInterface;

class DictionnaireService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    function getUnite(): array
    {
        return $this->em->getRepository(Entite::class)->findBy(["laboratoire" => true]);
    }
}
