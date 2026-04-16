<?php

namespace App\Security\Voter;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UtilisateurVoter extends Voter
{
    public const GetEnAttenteDeValidation = 'GetEnAttenteDeValidation';
    public const APPROUVER = 'APPROUVER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $a = in_array($attribute, [self::APPROUVER])
            && $subject instanceof \App\Entity\Utilisateur;

        $b = in_array($attribute, [self::GetEnAttenteDeValidation])
            && $subject === Utilisateur::class;

        return $a || $b;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::APPROUVER:
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
            case self::GetEnAttenteDeValidation:
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
        }

        return false;
    }
}
