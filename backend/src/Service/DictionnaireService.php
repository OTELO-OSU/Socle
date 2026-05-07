<?php

namespace App\Service;

use App\Entity\Corps;
use App\Entity\Entite;
use App\Entity\TypeContrat;
use Doctrine\ORM\EntityManagerInterface;

class DictionnaireService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    function getUnite(): array
    {
        return $this->em->getRepository(Entite::class)->findBy(["laboratoire" => true], ["nom" => "ASC"]);
    }

    function getCorps(): array
    {
        return $this->em->getRepository(Corps::class)->findBy([], ["nom" => "ASC"]);
    }

    function getTypeContrat(): array
    {
        return $this->em->getRepository(TypeContrat::class)->findBy([], ["nom" => "ASC"]);
    }
}
