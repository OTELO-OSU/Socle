<?php

namespace App\Security\Voter;

use App\Entity\Plateforme;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PlateformeVoter extends Voter
{
    public const CREER = 'CREER';
    public const AFFILIER = 'AFFILIER';
    public const TAG = 'TAG';
    public const LIRE = 'LIRE';
    public const SAUVEGARDER = 'SAUVEGARDER';
    public const EXPORTER = 'EXPORTER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $testA = in_array($attribute, [self::CREER])
            && $subject === Plateforme::class;

        $testB = in_array($attribute, [self::LIRE, self::SAUVEGARDER, self::EXPORTER, self::AFFILIER, self::TAG])
            && $subject instanceof Plateforme;
        return $testA || $testB;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::CREER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                break;
            case self::LIRE:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                /** @var Plateforme $subject */
                $unite = $subject->getLastHistorique()->getLaboratoireOperateur();
                if ($unite) {
                    if ($unite->getDirecteur() === $user) {
                        return true;
                    }

                    foreach ($unite->getDelegueDirecteur() as $d) {
                        if ($d === $user) {
                            return true;
                        }
                    }
                }
                $historiquePersonnel = $subject->getLastHistorique()->getPlateformeHistoriquePersonnels();
                foreach ($historiquePersonnel as $personnel) {
                    if ($personnel->getUtilisateur() === $user) {
                        if ($personnel->isResponsablePlateforme() || $personnel->isResponsableScientifique() || $personnel->isResponsableTechnique()) {
                            return true;
                        }
                    }
                }

                /** @var Utilisateur $user */
                $tagsPlateforme = $subject->getPlateformeTags()->map(fn($pt) => $pt->getTagId())->toArray();
                $tagsUtilisateur = $user->getUtilisateurTags()->map(fn($ut) => $ut->getTagId())->toArray();
                if (count(array_intersect($tagsPlateforme, $tagsUtilisateur)) > 0) {
                    return true;
                }
                break;
            case self::TAG:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                /** @var Plateforme $subject */
                $unite = $subject->getLastHistorique()->getLaboratoireOperateur();
                if ($unite) {
                    if ($unite->getDirecteur() === $user) {
                        return true;
                    }

                    foreach ($unite->getDelegueDirecteur() as $d) {
                        if ($d === $user) {
                            return true;
                        }
                    }
                }
                $historiquePersonnel = $subject->getLastHistorique()->getPlateformeHistoriquePersonnels();
                foreach ($historiquePersonnel as $personnel) {
                    if ($personnel->getUtilisateur() === $user) {
                        if ($personnel->isResponsablePlateforme() || $personnel->isResponsableScientifique() || $personnel->isResponsableTechnique()) {
                            return true;
                        }
                    }
                }
                break;
            case self::AFFILIER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;

                /** @var Plateforme $subject */
                $unite = $subject->getLastHistorique()->getLaboratoireOperateur();
                if ($unite) {
                    if ($unite->getDirecteur() === $user) {
                        return true;
                    }

                    foreach ($unite->getDelegueDirecteur() as $d) {
                        if ($d === $user) {
                            return true;
                        }
                    }
                }
                break;
            case self::SAUVEGARDER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                /** @var Plateforme $subject */
                $unite = $subject->getLastHistorique()->getLaboratoireOperateur();
                if ($unite) {
                    if ($unite->getDirecteur() === $user) {
                        return true;
                    }

                    foreach ($unite->getDelegueDirecteur() as $d) {
                        if ($d === $user) {
                            return true;
                        }
                    }
                }
                $historiquePersonnel = $subject->getLastHistorique()->getPlateformeHistoriquePersonnels();
                foreach ($historiquePersonnel as $personnel) {
                    if ($personnel->getUtilisateur() === $user) {
                        if ($personnel->isResponsablePlateforme() || $personnel->isResponsableScientifique() || $personnel->isResponsableTechnique()) {
                            return true;
                        }
                    }
                }

                /** @var Utilisateur $user */
                $tagsPlateforme = $subject->getPlateformeTags()->map(fn($pt) => $pt->getTagId())->toArray();
                $tagsUtilisateur = $user->getUtilisateurTags()->map(fn($ut) => $ut->getTagId())->toArray();
                if (count(array_intersect($tagsPlateforme, $tagsUtilisateur)) > 0) {
                    return true;
                }
                break;
            case self::EXPORTER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU', $user->getRoles())) return true;
                if (in_array('ROLE_DIR_OSU_DELEGUE', $user->getRoles())) return true;
                /** @var Plateforme $subject */
                $unite = $subject->getLastHistorique()->getLaboratoireOperateur();
                if ($unite) {
                    if ($unite->getDirecteur() === $user) {
                        return true;
                    }

                    foreach ($unite->getDelegueDirecteur() as $d) {
                        if ($d === $user) {
                            return true;
                        }
                    }
                }
                $historiquePersonnel = $subject->getLastHistorique()->getPlateformeHistoriquePersonnels();
                foreach ($historiquePersonnel as $personnel) {
                    if ($personnel->getUtilisateur() === $user) {
                        if ($personnel->isResponsablePlateforme() || $personnel->isResponsableScientifique() || $personnel->isResponsableTechnique()) {
                            return true;
                        }
                    }
                }

                /** @var Utilisateur $user */
                $tagsPlateforme = $subject->getPlateformeTags()->map(fn($pt) => $pt->getTagId())->toArray();
                $tagsUtilisateur = $user->getUtilisateurTags()->map(fn($ut) => $ut->getTagId())->toArray();
                if (count(array_intersect($tagsPlateforme, $tagsUtilisateur)) > 0) {
                    return true;
                }
                break;
        }

        return false;
    }
}
