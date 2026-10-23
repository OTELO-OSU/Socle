<?php

namespace App\Exception\auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UtilisateurNonAdmisException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct(
            "Votre compte n'a pas encore été approuvé. Un email vous sera envoyé une fois votre compte activé."
        );
    }
}
