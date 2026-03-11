<?php

namespace App\Exception\auth;

class EmailOuMotDePasseIncorrectException extends \Exception
{
    protected $message = "L'email ou le mot de passe est incorrect.";
    protected $code = 403;
}
