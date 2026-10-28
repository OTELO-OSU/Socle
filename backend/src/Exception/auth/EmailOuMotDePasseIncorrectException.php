<?php

namespace App\Exception\auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EmailOuMotDePasseIncorrectException extends UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct(
            'Bearer',
            "L'email ou le mot de passe est incorrect."
        );
    }
}
