<?php

namespace App\DTO;

use App\Validator\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Groupes :
 * inscription: nom, prenom, email et token sont obligatoires
 * affiliation: parmis unite_demande_id, plateforme_demande_id et osu_demande, un dois être renseigné.
 * oauth: token est obligatoire
 */

class UtilisateurInscriptionDTO
{
    #[NotBlank(groups: ['inscription'])]
    public ?string $nom = null;

    #[NotBlank(groups: ['inscription'])]
    public ?string $prenom = null;

    #[NotBlank(groups: ['inscription'])]
    #[Assert\Email(groups: ['inscription'])]
    public ?string $email = null;

    #[NotBlank(groups: ['basic'])]
    public ?string $mdp = null;

    #[EntityExists(entity: 'App\Entity\Entite', groups: ['affiliation'])]
    public ?int $unite_demande_id = null;

    #[EntityExists(entity: 'App\Entity\Plateforme', groups: ['affiliation'])]
    public ?int $plateforme_demande_id = null;

    public ?bool $osu_demande = null;

    #[NotBlank(groups: ['oauth'])]
    public ?string $token = null;

    #[Assert\Callback(groups: ['affiliation'])]
    public function validate(ExecutionContextInterface $context): void
    {
        $demande = 0;
        if ($this->plateforme_demande_id !== null) $demande++;
        if ($this->unite_demande_id !== null) $demande++;
        if ($this->osu_demande) $demande++;

        if ($demande === 0) {
            $context->buildViolation('Au moins une demande doit être faite (plateforme, unité ou OSU)')
                ->addViolation();
        } elseif ($demande > 1) {
            $context->buildViolation('Une seule demande doit être faite (plateforme, unité ou OSU)')
                ->addViolation();
        }
    }
}
