<?php

namespace App\Exception\auth;

class UtilisateurOrcidExistePasException extends \Exception
{
    protected $message = "Vous n'avez pas de compte lié à ce compte ORCID.";
    protected $code = 403;
}
