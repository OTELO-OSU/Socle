<?php

namespace App\Exception\auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class RefreshTokenInvalideOuExpireException extends UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct(
            'Bearer',
            "Le refresh token est invalide ou expiré."
        );
    }
}
