<?php

namespace App\Security\Voter;

use App\Entity\Plateforme;
use App\Entity\PlateformeEditeur;
use App\Entity\PlateformeHistorique;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PlateformeVoter extends Voter
{
    public const CREER = 'CREER';
    public const AFFILIER = 'AFFILIER';
    public const SAUVEGARDER = 'SAUVEGARDER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $testA = in_array($attribute, [self::CREER, self::AFFILIER])
            && $subject === Plateforme::class;

        $testB = in_array($attribute, [self::SAUVEGARDER])
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
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
            case self::AFFILIER:
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
            case self::SAUVEGARDER:
                if (in_array('ROLE_ADMIN', $user->getRoles())) return true;
                /** @var Plateforme $subject */
                return $subject->getPlateformeEditeurs()->contains(function (PlateformeEditeur $plateformeEditeur) use ($user) {
                    return $plateformeEditeur->getUtilisateur() == $user;
                });
                break;
        }

        return false;
    }
}
