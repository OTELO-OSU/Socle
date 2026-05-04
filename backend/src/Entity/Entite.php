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
    private ?string $statutJuridique = null;

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
    #[ORM\OneToMany(targetEntity: UtilisateurInscription::class, mappedBy: 'uniteDemande')]
    private Collection $utilisateurInscriptions;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    private ?Utilisateur $directeur = null;

    /**
     * @var Collection<int, PlateformeHistorique>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistorique::class, mappedBy: 'laboratoireOperateur')]
    private Collection $plateformeHistoriques;

    /**
     * @var Collection<int, PlateformeHistoriqueTutelle>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriqueTutelle::class, mappedBy: 'tutelle')]
    private Collection $plateformeHistoriqueTutelles;

    /**
     * @var Collection<int, PosteFutur>
     */
    #[ORM\OneToMany(targetEntity: PosteFutur::class, mappedBy: 'employeur')]
    private Collection $posteFuturs;

    /**
     * @var Collection<int, ContributionTutelle>
     */
    #[ORM\OneToMany(targetEntity: ContributionTutelle::class, mappedBy: 'financeur')]
    private Collection $contributionTutelles;

    /**
     * @var Collection<int, FinancementInstrument>
     */
    #[ORM\OneToMany(targetEntity: FinancementInstrument::class, mappedBy: 'financeur')]
    private Collection $financementInstruments;

    public function __construct()
    {
        $this->utilisateurInscriptions = new ArrayCollection();
        $this->plateformeHistoriques = new ArrayCollection();
        $this->plateformeHistoriqueTutelles = new ArrayCollection();
        $this->posteFuturs = new ArrayCollection();
        $this->contributionTutelles = new ArrayCollection();
        $this->financementInstruments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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
        return $this->statutJuridique;
    }

    public function setStatutJuridique(?string $statutJuridique): static
    {
        $this->statutJuridique = $statutJuridique;

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

    /**
     * @return Collection<int, PlateformeHistoriqueTutelle>
     */
    public function getPlateformeHistoriqueTutelles(): Collection
    {
        return $this->plateformeHistoriqueTutelles;
    }

    public function addPlateformeHistoriqueTutelle(PlateformeHistoriqueTutelle $plateformeHistoriqueTutelle): static
    {
        if (!$this->plateformeHistoriqueTutelles->contains($plateformeHistoriqueTutelle)) {
            $this->plateformeHistoriqueTutelles->add($plateformeHistoriqueTutelle);
            $plateformeHistoriqueTutelle->setTutelle($this);
        }

        return $this;
    }

    public function removePlateformeHistoriqueTutelle(PlateformeHistoriqueTutelle $plateformeHistoriqueTutelle): static
    {
        if ($this->plateformeHistoriqueTutelles->removeElement($plateformeHistoriqueTutelle)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriqueTutelle->getTutelle() === $this) {
                $plateformeHistoriqueTutelle->setTutelle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PosteFutur>
     */
    public function getPosteFuturs(): Collection
    {
        return $this->posteFuturs;
    }

    public function addPosteFutur(PosteFutur $posteFutur): static
    {
        if (!$this->posteFuturs->contains($posteFutur)) {
            $this->posteFuturs->add($posteFutur);
            $posteFutur->setEmployeur($this);
        }

        return $this;
    }

    public function removePosteFutur(PosteFutur $posteFutur): static
    {
        if ($this->posteFuturs->removeElement($posteFutur)) {
            // set the owning side to null (unless already changed)
            if ($posteFutur->getEmployeur() === $this) {
                $posteFutur->setEmployeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContributionTutelle>
     */
    public function getContributionTutelles(): Collection
    {
        return $this->contributionTutelles;
    }

    public function addContributionTutelle(ContributionTutelle $contributionTutelle): static
    {
        if (!$this->contributionTutelles->contains($contributionTutelle)) {
            $this->contributionTutelles->add($contributionTutelle);
            $contributionTutelle->setFinanceur($this);
        }

        return $this;
    }

    public function removeContributionTutelle(ContributionTutelle $contributionTutelle): static
    {
        if ($this->contributionTutelles->removeElement($contributionTutelle)) {
            // set the owning side to null (unless already changed)
            if ($contributionTutelle->getFinanceur() === $this) {
                $contributionTutelle->setFinanceur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FinancementInstrument>
     */
    public function getFinancementInstruments(): Collection
    {
        return $this->financementInstruments;
    }

    public function addFinancementInstrument(FinancementInstrument $financementInstrument): static
    {
        if (!$this->financementInstruments->contains($financementInstrument)) {
            $this->financementInstruments->add($financementInstrument);
            $financementInstrument->setFinanceur($this);
        }

        return $this;
    }

    public function removeFinancementInstrument(FinancementInstrument $financementInstrument): static
    {
        if ($this->financementInstruments->removeElement($financementInstrument)) {
            // set the owning side to null (unless already changed)
            if ($financementInstrument->getFinanceur() === $this) {
                $financementInstrument->setFinanceur(null);
            }
        }

        return $this;
    }
}
