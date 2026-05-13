<?php

namespace App\Service;

use App\Entity\Bap;
use App\Entity\Categorie;
use App\Entity\Cnu;
use App\Entity\Corps;
use App\Entity\Entite;
use App\Entity\Operation;
use App\Entity\SectionCnrs;
use App\Entity\TypeContrat;
use App\Entity\Ville;
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

    function getEmployeur(): array
    {
        return $this->em->getRepository(Entite::class)->findBy(["employeur" => true], ["nom" => "ASC"]);
    }

    function getCorps(): array
    {
        return $this->em->getRepository(Corps::class)->findBy([], ["nom" => "ASC"]);
    }

    function getTypeContrat(): array
    {
        return $this->em->getRepository(TypeContrat::class)->findBy([], ["nom" => "ASC"]);
    }

    function getVille(): array
    {
        return $this->em->getRepository(Ville::class)->findBy([], ["nom" => "ASC"]);
    }

    function getBaps(): array
    {
        return $this->em->getRepository(Bap::class)->findBy([], ["lettre" => "ASC"]);
    }

    function getCnu(): array
    {
        return $this->em->getRepository(Cnu::class)->findBy([], ["numero" => "ASC"]);
    }

    function getSectionsCNRS(): array
    {
        return $this->em->getRepository(SectionCnrs::class)->findBy([], ["numero" => "ASC"]);
    }

    function getTreeViewFinancement(): array
    {
        $cat = $this->em->getRepository(Categorie::class)->findBy([], ["nom" => "ASC"]);
        $noChild = [];
        $retour = [];
        foreach ($cat as $c) {
            $child = $c->getEntites()->toArray();
            usort($child, static function (Entite $a, Entite $b): int {
                return strcmp($a->getNom() ?? '', $b->getNom() ?? '');
            });
            $childArray = [];
            foreach ($child as $ch) {
                $childArray[] = [
                    "id" => $ch->getId(),
                    "label" => $ch->getNom(),
                ];
            }

            if (count($childArray) === 0) {
                continue;
            }

            if ($c->getNom() === "no-child") {
                $noChild = $childArray;
                continue;
            }
            $retour[] = [
                "id" => $c->getId() * -1,
                "label" => $c->getNom(),
                "children" => $childArray
            ];
        }

        $retour = array_merge($retour, $noChild);
        return $retour;
    }

    function getSourceFinancement(): array
    {
        return $this->em->getRepository(Entite::class)->findBy([], ["nom" => "ASC"]);
    }

    function getOperations(): array
    {
        return $this->em->getRepository(Operation::class)->findBy([], ["nom" => "ASC"]);
    }
}
