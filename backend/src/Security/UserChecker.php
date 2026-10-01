<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        if (!$user->isEmailValide()) {
            throw new Exception('Votre compte n\'est pas encore activé. Veuillez attendre l\'activation de votre compte.');
        }

        if (!$user->isAdmis()) {
            throw new Exception('Votre compte n\'est pas approuvé.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }
    }
}
