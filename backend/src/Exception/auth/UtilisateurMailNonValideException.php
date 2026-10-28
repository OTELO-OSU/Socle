<?php

namespace App\Exception\auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UtilisateurMailNonValideException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct(
            "Vous n'avez pas validé votre adresse email. Un email de validation vous a été renvoyé."
        );
    }
}
