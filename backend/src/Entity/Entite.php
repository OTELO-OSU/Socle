<?php

namespace App\Entity;

use App\Repository\EntiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: EntiteRepository::class)]
class Entite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    #[Groups(['unite:list', 'employeur:list', 'utilisateur:list', 'source_financement:list', 'universite:list'])]
    private ?int $id = null;

    #[Groups(['unite:list', 'employeur:list', 'utilisateur:list', 'source_financement:list', 'universite:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['unite:list', 'employeur:list', 'utilisateur:list', 'source_financement:list', 'universite:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acronyme = null;

    #[Groups(['unite:list', 'employeur:list', 'utilisateur:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statutJuridique = null;

    #[ORM\Column]
    private ?bool $employeur = null;

    #[ORM\Column]
    private ?bool $laboratoire = null;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    #[Groups(["utilisateur:list", "unite:detail"])]
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

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'unite')]
    private Collection $utilisateurs;

    #[Groups(['unite:list', 'utilisateur:list', 'plateforme:harvester'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeUnite = null;

    #[Groups(['unite:list', 'unite:detail'])]
    #[SerializedName('ror_id')]
    #[ORM\Column(length: 9, unique: true, nullable: true)]
    private ?string $rorId = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'employeur')]
    private Collection $employe;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'universiteRattachement')]
    private Collection $doctorants;

    #[ORM\Column]
    private ?bool $universite = null;

    #[Groups(['employeur:list', 'utilisateur:list'])]
    #[ORM\Column]
    private ?bool $epic = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[SerializedName("delegues")]
    #[Groups(['unite:detail'])]
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'deleguesDirecteur')]
    private Collection $delegueDirecteur;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero_rnsr = null;

    public function __construct()
    {
        $this->plateformeHistoriques = new ArrayCollection();
        $this->plateformeHistoriqueTutelles = new ArrayCollection();
        $this->posteFuturs = new ArrayCollection();
        $this->contributionTutelles = new ArrayCollection();
        $this->financementInstruments = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->employe = new ArrayCollection();
        $this->doctorants = new ArrayCollection();
        $this->delegueDirecteur = new ArrayCollection();
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

    #[Groups(["unite:detail"])]
    #[SerializedName("directeur_id")]
    public function getDirecteurId(): ?int
    {
        return $this->directeur?->getId();
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

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setUnite($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getUnite() === $this) {
                $utilisateur->setUnite(null);
            }
        }

        return $this;
    }

    public function getCodeUnite(): ?string
    {
        return $this->codeUnite;
    }

    public function setCodeUnite(?string $codeUnite): static
    {
        $this->codeUnite = $codeUnite;

        return $this;
    }

    public function getRorId(): ?string
    {
        return $this->rorId;
    }

    public function setRorId(?string $rorId): static
    {
        $this->rorId = $rorId;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getEmploye(): Collection
    {
        return $this->employe;
    }

    public function addEmploye(Utilisateur $employe): static
    {
        if (!$this->employe->contains($employe)) {
            $this->employe->add($employe);
            $employe->setEmployeur($this);
        }

        return $this;
    }

    public function removeEmploye(Utilisateur $employe): static
    {
        if ($this->employe->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getEmployeur() === $this) {
                $employe->setEmployeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getDoctorants(): Collection
    {
        return $this->doctorants;
    }

    public function addDoctorant(Utilisateur $doctorant): static
    {
        if (!$this->doctorants->contains($doctorant)) {
            $this->doctorants->add($doctorant);
            $doctorant->setUniversiteRattachement($this);
        }

        return $this;
    }

    public function removeDoctorant(Utilisateur $doctorant): static
    {
        if ($this->doctorants->removeElement($doctorant)) {
            // set the owning side to null (unless already changed)
            if ($doctorant->getUniversiteRattachement() === $this) {
                $doctorant->setUniversiteRattachement(null);
            }
        }

        return $this;
    }

    public function isUniversite(): ?bool
    {
        return $this->universite;
    }

    public function setUniversite(bool $universite): static
    {
        $this->universite = $universite;

        return $this;
    }

    public function isEpic(): ?bool
    {
        return $this->epic;
    }

    public function setEpic(bool $epic): static
    {
        $this->epic = $epic;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getDelegueDirecteur(): Collection
    {
        return $this->delegueDirecteur;
    }

    public function addDelegueDirecteur(Utilisateur $delegueDirecteur): static
    {
        if (!$this->delegueDirecteur->contains($delegueDirecteur)) {
            $this->delegueDirecteur->add($delegueDirecteur);
        }

        return $this;
    }

    public function removeDelegueDirecteur(Utilisateur $delegueDirecteur): static
    {
        $this->delegueDirecteur->removeElement($delegueDirecteur);

        return $this;
    }

    public function estUtilisateurDirOuDirDeleg(Utilisateur $user): bool
    {
        $dirUnite = $this->getDirecteur();
        if ($dirUnite == $user) {
            return true;
        }

        $dirDelegUnite = $this->getDelegueDirecteur();
        if ($dirDelegUnite->contains($user)) {
            return true;
        }

        return false;
    }

    public function getNumeroRnsr(): ?string
    {
        return $this->numero_rnsr;
    }

    public function setNumeroRnsr(?string $numero_rnsr): static
    {
        $this->numero_rnsr = $numero_rnsr;

        return $this;
    }
}
