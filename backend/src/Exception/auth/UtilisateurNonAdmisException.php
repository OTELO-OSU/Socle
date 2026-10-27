<?php

namespace App\Exception\auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UtilisateurNonAdmisException extends UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct(
            'Bearer',
            "Votre compte n'a pas encore été approuvé. Un email vous sera envoyé une fois votre compte activé."
        );
    }
}
