<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plateforme:list', 'utilisateur:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plateforme:list', 'utilisateur:list', "plateforme:detail"])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plateforme:list', 'utilisateur:list', "plateforme:detail"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups(['plateforme:list', 'utilisateur:list', "plateforme:detail"])]
    private ?string $email = null;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mdp = null;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subOrcid = null;

    #[Ignore]
    #[ORM\Column]
    private ?bool $emailValide = null;

    #[Ignore]
    #[ORM\Column]
    private ?bool $admis = null;

    #[Ignore]
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var Collection<int, RefreshToken>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private Collection $refreshTokens;

    #[Ignore]
    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurVerification $utilisateurVerification = null;

    #[Ignore]
    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurMachine $utilisateurMachine = null;

    #[Ignore]
    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurInscription $utilisateurInscription = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private Collection $plateformeHistoriquePersonnels;

    /**
     * @var Collection<int, Entite>
     */
    #[ORM\OneToMany(targetEntity: Entite::class, mappedBy: 'directeur')]
    private Collection $entites;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Corps $corps = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Grade $grade = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?SectionCnrs $sectionCnrs = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Cnu $cnu = null;

    /**
     * @var Collection<int, Bap>
     */
    #[ORM\ManyToMany(targetEntity: Bap::class)]
    private Collection $baps;

    /**
     * @var Collection<int, EmploiType>
     */
    #[ORM\ManyToMany(targetEntity: EmploiType::class)]
    private Collection $emploiTypes;

    #[ORM\Column]
    private bool $attenteApprobationDU = false;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Entite $unite = null;

    #[ORM\ManyToOne(inversedBy: 'employe')]
    private ?Entite $employeur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    #[SerializedName("type_contrat")]
    private ?TypeContrat $typeContrat = null;

    #[Groups(['utilisateur:list', "plateforme:detail"])]
    #[ORM\Column(nullable: true)]
    private ?bool $doctorant = null;

    #[SerializedName("universite_rattachement")]
    #[ORM\ManyToOne(inversedBy: 'doctorants')]
    private ?Entite $universiteRattachement = null;

    #[ORM\ManyToMany(targetEntity: Entite::class, mappedBy: 'delegueDirecteur')]
    private Collection $deleguesDirecteur;

    #[ORM\OneToMany(targetEntity: UtilisateurTags::class, mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private Collection $utilisateurTags;

    public function __construct()
    {
        $this->emailValide = false;
        $this->admis = false;
        $this->refreshTokens = new ArrayCollection();
        $this->plateformeHistoriquePersonnels = new ArrayCollection();
        $this->entites = new ArrayCollection();
        $this->baps = new ArrayCollection();
        $this->emploiTypes = new ArrayCollection();
        $this->deleguesDirecteur = new ArrayCollection();
        $this->utilisateurTags = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    #[Ignore]
    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(?string $mdp): static
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function getSubOrcid(): ?string
    {
        return $this->subOrcid;
    }

    public function setSubOrcid(?string $subOrcid): static
    {
        $this->subOrcid = $subOrcid;

        return $this;
    }

    public function isEmailValide(): ?bool
    {
        return $this->emailValide;
    }

    public function setEmailValide(bool $emailValide): static
    {
        $this->emailValide = $emailValide;

        return $this;
    }

    public function isAdmis(): ?bool
    {
        return $this->admis;
    }

    public function setAdmis(bool $admis): static
    {
        $this->admis = $admis;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Sur une base de JWT pas besoin
    }

    #[Ignore]
    public function getPassword(): ?string
    {
        return $this->mdp;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            // set the owning side to null (unless already changed)
            if ($refreshToken->getUtilisateur() === $this) {
                $refreshToken->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getUtilisateurVerification(): ?UtilisateurVerification
    {
        return $this->utilisateurVerification;
    }

    public function setUtilisateurVerification(?UtilisateurVerification $utilisateurVerification): static
    {
        if ($utilisateurVerification !== null && $utilisateurVerification->getUtilisateur() !== $this) {
            $utilisateurVerification->setUtilisateur($this);
        }

        $this->utilisateurVerification = $utilisateurVerification;

        return $this;
    }

    public function getUtilisateurMachine(): ?UtilisateurMachine
    {
        return $this->utilisateurMachine;
    }

    public function setUtilisateurMachine(UtilisateurMachine $utilisateurMachine): static
    {
        // set the owning side of the relation if necessary
        if ($utilisateurMachine->getUtilisateur() !== $this) {
            $utilisateurMachine->setUtilisateur($this);
        }

        $this->utilisateurMachine = $utilisateurMachine;

        return $this;
    }

    public function getUtilisateurInscription(): ?UtilisateurInscription
    {
        return $this->utilisateurInscription;
    }

    public function setUtilisateurInscription(UtilisateurInscription $utilisateurInscription): static
    {
        // set the owning side of the relation if necessary
        if ($utilisateurInscription->getUtilisateur() !== $this) {
            $utilisateurInscription->setUtilisateur($this);
        }

        $this->utilisateurInscription = $utilisateurInscription;

        return $this;
    }

    /**
     * @return Collection<int, PlateformeHistoriquePersonnel>
     */
    public function getPlateformeHistoriquePersonnels(): Collection
    {
        return $this->plateformeHistoriquePersonnels;
    }

    public function addPlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if (!$this->plateformeHistoriquePersonnels->contains($plateformeHistoriquePersonnel)) {
            $this->plateformeHistoriquePersonnels->add($plateformeHistoriquePersonnel);
            $plateformeHistoriquePersonnel->setUtilisateur($this);
        }

        return $this;
    }

    public function removePlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if ($this->plateformeHistoriquePersonnels->removeElement($plateformeHistoriquePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriquePersonnel->getUtilisateur() === $this) {
                $plateformeHistoriquePersonnel->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Entite>
     */
    public function getEntites(): Collection
    {
        return $this->entites;
    }

    public function addEntite(Entite $entite): static
    {
        if (!$this->entites->contains($entite)) {
            $this->entites->add($entite);
            $entite->setDirecteur($this);
        }

        return $this;
    }

    public function removeEntite(Entite $entite): static
    {
        if ($this->entites->removeElement($entite)) {
            // set the owning side to null (unless already changed)
            if ($entite->getDirecteur() === $this) {
                $entite->setDirecteur(null);
            }
        }

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("corps_id")]
    public function getCorpsId(): ?int
    {
        return $this->corps ? $this->corps->getId() : null;
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

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("grade_id")]
    public function getGradeId(): ?int
    {
        return $this->grade ? $this->grade->getId() : null;
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

    public function getSectionCnrs(): ?SectionCnrs
    {
        return $this->sectionCnrs;
    }

    #[SerializedName("section_cnrs_id")]
    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    public function getSectionCnrsId(): ?int
    {
        return $this->sectionCnrs ? $this->sectionCnrs->getId() : null;
    }

    public function setSectionCnrs(?SectionCnrs $sectionCnrs): static
    {
        $this->sectionCnrs = $sectionCnrs;

        return $this;
    }

    public function getCnu(): ?Cnu
    {
        return $this->cnu;
    }

    #[SerializedName("cnu_id")]
    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    public function getCnuId(): ?int
    {
        return $this->cnu ? $this->cnu->getId() : null;
    }

    public function setCnu(?Cnu $cnu): static
    {
        $this->cnu = $cnu;

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("baps_ids")]
    public function getBapsIds(): array
    {
        return $this->baps->map(fn(Bap $bap) => $bap->getId())->toArray();
    }

    /**
     * @return Collection<int, Bap>
     */
    public function getBaps(): Collection
    {
        return $this->baps;
    }

    public function addBap(Bap $bap): static
    {
        if (!$this->baps->contains($bap)) {
            $this->baps->add($bap);
        }

        return $this;
    }

    public function removeBap(Bap $bap): static
    {
        $this->baps->removeElement($bap);
        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("emploi_types_ids")]
    public function getEmploiTypesIds(): array
    {
        return $this->emploiTypes->map(fn(EmploiType $emploiType) => $emploiType->getId())->toArray();
    }


    /**
     * @return Collection<int, EmploiType>
     */
    public function getEmploiTypes(): Collection
    {
        return $this->emploiTypes;
    }

    public function addEmploiType(EmploiType $emploiType): static
    {
        if (!$this->emploiTypes->contains($emploiType)) {
            $this->emploiTypes->add($emploiType);
        }

        return $this;
    }

    public function removeEmploiType(EmploiType $emploiType): static
    {
        $this->emploiTypes->removeElement($emploiType);
        return $this;
    }

    public function isAttenteApprobationDU(): bool
    {
        return $this->attenteApprobationDU;
    }

    public function setAttenteApprobationDU(bool $attenteApprobationDU): static
    {
        $this->attenteApprobationDU = $attenteApprobationDU;

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("unite_id")]
    public function getUniteId(): ?int
    {
        return $this->unite ? $this->unite->getId() : null;
    }

    public function getUnite(): ?Entite
    {
        return $this->unite;
    }

    public function setUnite(?Entite $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("employeur_id")]
    public function getEmployeurId(): ?int
    {
        return $this->employeur ? $this->employeur->getId() : null;
    }

    public function getEmployeur(): ?Entite
    {
        return $this->employeur;
    }

    public function setEmployeur(?Entite $employeur): static
    {
        $this->employeur = $employeur;

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("type_contrat_id")]
    public function getTypeContratId(): ?int
    {
        return $this->typeContrat ? $this->typeContrat->getId() : null;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(?TypeContrat $typeContrat): static
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function isDoctorant(): ?bool
    {
        return $this->doctorant;
    }

    public function setDoctorant(bool $doctorant): static
    {
        $this->doctorant = $doctorant;

        return $this;
    }

    #[Groups(['utilisateur:list', 'plateforme:detail'])]
    #[SerializedName("universite_rattachement_id")]
    public function getUniversiteRattachementId(): ?int
    {
        return $this->universiteRattachement ? $this->universiteRattachement->getId() : null;
    }

    public function getUniversiteRattachement(): ?Entite
    {
        return $this->universiteRattachement;
    }

    public function setUniversiteRattachement(?Entite $universiteRattachement): static
    {
        $this->universiteRattachement = $universiteRattachement;

        return $this;
    }

    public function getDeleguesDirecteur(): Collection
    {
        return $this->deleguesDirecteur;
    }

    public function getUtilisateurTags(): Collection
    {
        return $this->utilisateurTags;
    }

    public function addUtilisateurTag(UtilisateurTags $utilisateurTag): static
    {
        if (!$this->utilisateurTags->contains($utilisateurTag)) {
            $this->utilisateurTags->add($utilisateurTag);
        }

        return $this;
    }

    public function removeUtilisateurTag(UtilisateurTags $utilisateurTag): static
    {
        $this->utilisateurTags->removeElement($utilisateurTag);
        return $this;
    }
}
