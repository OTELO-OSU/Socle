<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UtilisateurVoter extends Voter
{
    public const ADMETTRE = 'ADMETTRE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ADMETTRE])
            && $subject instanceof \App\Entity\Utilisateur;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::ADMETTRE:
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
        }

        return false;
    }
}
