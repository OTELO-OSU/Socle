<?php

namespace App\Exception\auth;

class UtilisateurFedeExistePasException extends \Exception
{
    protected $payload;
    protected $message = "Vous n'avez pas de compte lié à ce compte de fédération. Veuillez vous inscrire en utilisant ce compte de fédération pour créer un compte lié.";
    protected $code = 403;

    public function __construct(string $email, ?string $prenom, ?string $nom)
    {
        parent::__construct();
        $this->payload = \base64_encode(json_encode([
            "email" => $email,
            "prenom" => $prenom,
            "nom" => $nom
        ]));
    }

    public function getPayload(): string
    {
        return $this->payload;
    }
}
