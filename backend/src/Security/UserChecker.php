<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        if (!$user->isEmailValide()) {
            throw new AccessDeniedException($this->translator->trans('exception.auth.email_non_verifie'));
        }

        if (!$user->isAdmis()) {
            throw new AccessDeniedException($this->translator->trans('exception.auth.compte_non_approuve'));
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }
    }
}
