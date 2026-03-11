<?php

namespace App\Exception\auth;

class RefreshTokenInvalideOuExpireException extends \Exception
{
    protected $message = "Le refresh token est invalide ou expiré.";
    protected $code = 403;
}
