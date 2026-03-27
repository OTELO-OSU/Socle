<?php

namespace App\Entity;

use App\Repository\EntiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntiteRepository::class)]
class Entite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acronyme = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut_juridique = null;

    #[ORM\Column]
    private ?bool $employeur = null;

    #[ORM\Column]
    private ?bool $laboratoire = null;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    /**
     * @var Collection<int, UtilisateurInscription>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurInscription::class, mappedBy: 'unite_demande')]
    private Collection $utilisateurInscriptions;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    private ?Utilisateur $directeur = null;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'laboratoire_operateur')]
    private Collection $plateformeHistoriques;

    public function __construct()
    {
        $this->utilisateurInscriptions = new ArrayCollection();
        $this->plateformeHistoriques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAcronyme(): ?string
    {
        return $this->acronyme;
    }

    public function setAcronyme(?string $acronyme): static
    {
        $this->acronyme = $acronyme;

        return $this;
    }

    public function getStatutJuridique(): ?string
    {
        return $this->statut_juridique;
    }

    public function setStatutJuridique(?string $statut_juridique): static
    {
        $this->statut_juridique = $statut_juridique;

        return $this;
    }

    public function isEmployeur(): ?bool
    {
        return $this->employeur;
    }

    public function setEmployeur(bool $employeur): static
    {
        $this->employeur = $employeur;

        return $this;
    }

    public function isLaboratoire(): ?bool
    {
        return $this->laboratoire;
    }

    public function setLaboratoire(bool $laboratoire): static
    {
        $this->laboratoire = $laboratoire;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

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
            $utilisateurInscription->setUniteDemande($this);
        }

        return $this;
    }

    public function removeUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        if ($this->utilisateurInscriptions->removeElement($utilisateurInscription)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurInscription->getUniteDemande() === $this) {
                $utilisateurInscription->setUniteDemande(null);
            }
        }

        return $this;
    }

    public function getDirecteur(): ?Utilisateur
    {
        return $this->directeur;
    }

    public function setDirecteur(?Utilisateur $directeur): static
    {
        $this->directeur = $directeur;

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
            $plateformeHistorique->setLaboratoireOperateur($this);
        }

        return $this;
    }

    public function removePlateformeHistorique(PlateformeHistorique $plateformeHistorique): static
    {
        if ($this->plateformeHistoriques->removeElement($plateformeHistorique)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistorique->getLaboratoireOperateur() === $this) {
                $plateformeHistorique->setLaboratoireOperateur(null);
            }
        }

        return $this;
    }
}
