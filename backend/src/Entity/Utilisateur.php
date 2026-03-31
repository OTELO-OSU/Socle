<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mdp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sub_orcid = null;

    #[ORM\Column]
    private ?bool $email_valide = null;

    #[ORM\Column]
    private ?bool $admis = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var Collection<int, RefreshToken>
     */
    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private Collection $refreshTokens;

    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurVerification $utilisateurVerification = null;

    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurMachine $utilisateurMachine = null;

    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?UtilisateurInscription $utilisateurInscription = null;

    /**
     * @var Collection<int, PlateformeHistoriquePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PlateformeHistoriquePersonnel::class, mappedBy: 'utilisateur_id')]
    private Collection $plateformeHistoriquePersonnels;

    /**
     * @var Collection<int, PlateformeEditeur>
     */
    #[ORM\OneToMany(targetEntity: PlateformeEditeur::class, mappedBy: 'utilisateur')]
    private Collection $plateformeEditeurs;

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
    private ?SectionCnrs $section_cnrs = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Cnu $cnu = null;

    /**
     * @var Collection<int, UtilisateurBap>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurBap::class, mappedBy: 'utilisateur')]
    private Collection $utilisateurBaps;

    /**
     * @var Collection<int, UtilisateurEmploiType>
     */
    #[ORM\OneToMany(targetEntity: UtilisateurEmploiType::class, mappedBy: 'utilisateur')]
    private Collection $utilisateurEmploiTypes;

    public function __construct()
    {
        $this->email_valide = false;
        $this->admis = false;
        $this->refreshTokens = new ArrayCollection();
        $this->plateformeHistoriquePersonnels = new ArrayCollection();
        $this->plateformeEditeurs = new ArrayCollection();
        $this->entites = new ArrayCollection();
        $this->utilisateurBaps = new ArrayCollection();
        $this->utilisateurEmploiTypes = new ArrayCollection();
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
        return $this->sub_orcid;
    }

    public function setSubOrcid(?string $sub_orcid): static
    {
        $this->sub_orcid = $sub_orcid;

        return $this;
    }

    public function isEmailValide(): ?bool
    {
        return $this->email_valide;
    }

    public function setEmailValide(bool $email_valide): static
    {
        $this->email_valide = $email_valide;

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
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Sur une base de JWT pas besoin
    }

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

    public function setUtilisateurVerification(UtilisateurVerification $utilisateurVerification): static
    {
        // set the owning side of the relation if necessary
        if ($utilisateurVerification->getUtilisateur() !== $this) {
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
            $plateformeHistoriquePersonnel->setUtilisateurId($this);
        }

        return $this;
    }

    public function removePlateformeHistoriquePersonnel(PlateformeHistoriquePersonnel $plateformeHistoriquePersonnel): static
    {
        if ($this->plateformeHistoriquePersonnels->removeElement($plateformeHistoriquePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($plateformeHistoriquePersonnel->getUtilisateurId() === $this) {
                $plateformeHistoriquePersonnel->setUtilisateurId(null);
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
            $plateformeEditeur->setUtilisateur($this);
        }

        return $this;
    }

    public function removePlateformeEditeur(PlateformeEditeur $plateformeEditeur): static
    {
        if ($this->plateformeEditeurs->removeElement($plateformeEditeur)) {
            // set the owning side to null (unless already changed)
            if ($plateformeEditeur->getUtilisateur() === $this) {
                $plateformeEditeur->setUtilisateur(null);
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

    public function getSectionCnrs(): ?SectionCnrs
    {
        return $this->section_cnrs;
    }

    public function setSectionCnrs(?SectionCnrs $section_cnrs): static
    {
        $this->section_cnrs = $section_cnrs;

        return $this;
    }

    public function getCnu(): ?Cnu
    {
        return $this->cnu;
    }

    public function setCnu(?Cnu $cnu): static
    {
        $this->cnu = $cnu;

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurBap>
     */
    public function getUtilisateurBaps(): Collection
    {
        return $this->utilisateurBaps;
    }

    public function addUtilisateurBap(UtilisateurBap $utilisateurBap): static
    {
        if (!$this->utilisateurBaps->contains($utilisateurBap)) {
            $this->utilisateurBaps->add($utilisateurBap);
            $utilisateurBap->setUtilisateur($this);
        }

        return $this;
    }

    public function removeUtilisateurBap(UtilisateurBap $utilisateurBap): static
    {
        if ($this->utilisateurBaps->removeElement($utilisateurBap)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurBap->getUtilisateur() === $this) {
                $utilisateurBap->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurEmploiType>
     */
    public function getUtilisateurEmploiTypes(): Collection
    {
        return $this->utilisateurEmploiTypes;
    }

    public function addUtilisateurEmploiType(UtilisateurEmploiType $utilisateurEmploiType): static
    {
        if (!$this->utilisateurEmploiTypes->contains($utilisateurEmploiType)) {
            $this->utilisateurEmploiTypes->add($utilisateurEmploiType);
            $utilisateurEmploiType->setUtilisateur($this);
        }

        return $this;
    }

    public function removeUtilisateurEmploiType(UtilisateurEmploiType $utilisateurEmploiType): static
    {
        if ($this->utilisateurEmploiTypes->removeElement($utilisateurEmploiType)) {
            // set the owning side to null (unless already changed)
            if ($utilisateurEmploiType->getUtilisateur() === $this) {
                $utilisateurEmploiType->setUtilisateur(null);
            }
        }

        return $this;
    }
}
