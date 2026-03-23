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

    public function __construct()
    {
        $this->email_valide = false;
        $this->admis = false;
        $this->refreshTokens = new ArrayCollection();
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
}
