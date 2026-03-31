<?php

namespace App\Entity;

use App\Repository\CorpsGradeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CorpsGradeRepository::class)]
class CorpsGrade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'corpsGrades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corps $corps = null;

    #[ORM\ManyToOne(inversedBy: 'corpsGrades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Grade $grade = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorps(): ?Corps
    {
        return $this->corps;
    }

    public function setCorps(?Corps $corps): static
    {
        $this->corps = $corps;

        return $this;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): static
    {
        $this->grade = $grade;

        return $this;
    }
}
