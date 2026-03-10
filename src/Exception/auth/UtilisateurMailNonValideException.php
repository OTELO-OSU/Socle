<?php

namespace App\Exception\auth;

class UtilisateurMailNonValideException extends \Exception
{
    protected $message = "Vous n'avez pas validé votre adresse email. Un email de validation vous a été renvoyé.";
    protected $code = 403;
}
