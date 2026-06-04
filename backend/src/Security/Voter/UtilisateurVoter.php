<?php

namespace App\Security\Voter;

use App\Entity\Entite;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UtilisateurVoter extends Voter
{
    public const GetEnAttenteDeValidation = 'GetEnAttenteDeValidation';
    public const GERER_EN_ATTENTE_DE_VALIDATION_DU_DU = 'GERER_EN_ATTENTE_DE_VALIDATION_DU_DU';
    public const GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE = 'GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE';
    public const APPROUVER = 'APPROUVER';
    public const MODIFIER = 'MODIFIER';
    public const MODIFIER_EMAIL = 'MODIFIER_EMAIL';
    public const GERER_ANNEE_SAISIE = 'GERER_ANNEE_SAISIE';
    public const CHANGER_DU = 'CHANGER_DU';
    public const CHANGER_DELEGUE_DU = 'CHANGER_DELEGUE_DU';
    public const GET_ADMIN_OSU = 'GET_ADMIN_OSU';
    public const CHANGER_DO = 'CHANGER_DO';
    public const CHANGER_DELEGUE_DO = 'CHANGER_DELEGUE_DO';
    public const SOUMETTRE_AU_DU = 'SOUMETTRE_AU_DU';
    public const SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE = 'SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $a = in_array($attribute, [self::APPROUVER, self::MODIFIER, self::MODIFIER_EMAIL])
            && $subject instanceof \App\Entity\Utilisateur;

        $b = in_array($attribute, [self::GetEnAttenteDeValidation, self::GET_ADMIN_OSU, self::CHANGER_DU, self::CHANGER_DO, self::CHANGER_DELEGUE_DO, self::SOUMETTRE_AU_DU, self::SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE, self::GERER_EN_ATTENTE_DE_VALIDATION_DU_DU, self::GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE, self::GERER_ANNEE_SAISIE])
            && $subject === Utilisateur::class;

        $c = in_array($attribute, [self::CHANGER_DELEGUE_DU])
            && $subject instanceof Entite;

        return $a || $b || $c;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::APPROUVER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::GERER_ANNEE_SAISIE:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::CHANGER_DU:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::CHANGER_DELEGUE_DU:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                /** @var Entite $subject */
                if ($subject->getDirecteur() === $user) {
                    return true;
                }
                break;
            case self::GET_ADMIN_OSU:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::CHANGER_DO:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                break;
            case self::CHANGER_DELEGUE_DO:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                break;
            case self::MODIFIER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;

                /** @var Utilisateur $subject */
                if ($user->getId() === $subject->getId()) {
                    return true;
                }
                break;
            case self::MODIFIER_EMAIL:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::GetEnAttenteDeValidation:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::GERER_EN_ATTENTE_DE_VALIDATION_DU_DU:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                $du = $user->getEntites()->filter(function ($e) {
                    return $e->isLaboratoire();
                })->first();
                if ($du) {
                    return true;
                }
                break;
            case self::GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::SOUMETTRE_AU_DU:
                $plateformeHistorique = $user->getPlateformeHistoriquePersonnels();
                foreach ($plateformeHistorique as $ph) {
                    if ($ph->isResponsableScientifique() || $ph->isResponsableTechnique() || $ph->isResponsablePlateforme()) {
                        $plateformeHisto = $ph->getPlateformeHistorique();
                        $plateformeMere = $plateformeHisto->getPlateforme();
                        $lastVersion = $plateformeMere->getLastHistorique();
                        if ($plateformeHisto->getId() === $lastVersion->getId()) {
                            return true;
                        }
                    }
                }
                break;
            case self::SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE:
                $plateformeHistorique = $user->getPlateformeHistoriquePersonnels();
                foreach ($plateformeHistorique as $ph) {
                    if ($ph->isResponsableScientifique() || $ph->isResponsableTechnique() || $ph->isResponsablePlateforme()) {
                        $plateformeHisto = $ph->getPlateformeHistorique();
                        $plateformeMere = $plateformeHisto->getPlateforme();
                        $lastVersion = $plateformeMere->getLastHistorique();
                        if ($plateformeHisto->getId() === $lastVersion->getId()) {
                            // On fait la liste des personnels dans cette unité pour vérifier si l'un est dans l'uar
                            $toutPersonnel = $plateformeHisto->getPlateformeHistoriquePersonnels();
                            foreach ($toutPersonnel as $personnel) {
                                if ($personnel->isResponsableScientifique() || $personnel->isResponsableTechnique() || $personnel->isResponsablePlateforme()) {
                                    $codeUnite = $personnel->getUtilisateur()->getUnite()->getCodeUnite();
                                    if ($codeUnite && str_starts_with($codeUnite, 'UAR')) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }

        return false;
    }
}
