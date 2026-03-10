<?php

namespace App\Exception\auth;

class UtilisateurNonAdmisException extends \Exception
{
    protected $message = "Votre compte n'a pas encore été admis. Un email vous sera envoyé une fois votre compte activé.";
    protected $code = 403;
}
