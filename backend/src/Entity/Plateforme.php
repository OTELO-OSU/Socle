<?php

namespace App\Entity;

use App\Repository\PlateformeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateformeRepository::class)]
class Plateforme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pseudo_id = null;

    /**
     * @var Collection<int, UtilisateurInscription>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurInscription::class, mappedBy: 'plateforme_demande')]
    private Collection $utilisateurInscriptions;

    /**
     * @var Collection<int, PlateformeEditeur>
     */
    #[ORM\OneToMany(targetEntity: PlateformeEditeur::class, mappedBy: 'plateforme_id')]
    private Collection $plateformeEditeurs;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'plateforme')]
    private Collection $plateformeHistoriques;

    public function __construct()
    {
        $this->utilisateurInscriptions = new ArrayCollection();
        $this->plateformeEditeurs = new ArrayCollection();
        $this->plateformeHistoriques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudoId(): ?string
    {
        return $this->pseudo_id;
    }

    public function setPseudoId(?string $pseudo_id): static
    {
        $this->pseudo_id = $pseudo_id;

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurInscription>
     */
    public function getUtilisateurInscriptions(): Collection
    {
        return $this->utilisateurInscriptions;
    }

    public function addUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        if (!$this->utilisateurInscriptions->contains($utilisateurInscription)) {
            $this->utilisateurInscriptions->add($utilisateurInscription);
            $utilisateurInscription->setPlateformeDemande($this);
        }

        return $this;
    }

    public function removeUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        if ($this->utilisateurInscriptions->removeElement($utilisateurInscription)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurInscription->getPlateformeDemande() === $this) {
                $utilisateurInscription->setPlateformeDemande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlateformeEditeur>
     */
    public function getPlateformeEditeurs(): Collection
    {
        return $this->plateformeEditeurs;
    }

    public function addPlateformeEditeur(PlateformeEditeur $plateformeEditeur): static
    {
        if (!$this->plateformeEditeurs->contains($plateformeEditeur)) {
            $this->plateformeEditeurs->add($plateformeEditeur);
            $plateformeEditeur->setPlateforme($this);
        }

        return $this;
    }

    public function removePlateformeEditeur(PlateformeEditeur $plateformeEditeur): static
    {
        if ($this->plateformeEditeurs->removeElement($plateformeEditeur)) {
            // set the owning side to null (unless already changed)
            if ($plateformeEditeur->getPlateforme() === $this) {
                $plateformeEditeur->setPlateforme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistorique>
     */
    public function getPlateformeHistoriques(): Collection
    {
        return $this->plateformeHistoriques;
    }

    public function addPlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if (!$this->plateformeHistoriques->contains($plateformeHistorique)) {
            $this->plateformeHistoriques->add($plateformeHistorique);
            $plateformeHistorique->setPlateforme($this);
        }

        return $this;
    }

    public function removePlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if ($this->plateformeHistoriques->removeElement($plateformeHistorique)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistorique->getPlateforme() === $this) {
                $plateformeHistorique->setPlateforme(null);
            }
        }

        return $this;
    }
}
