<?php

namespace App\Exception\auth;

class UtilisateurOAuthExistePasException extends \Exception
{
    protected $payload;
    protected $token;
    protected $message = "Votre compte n'existe pas encore. Veuillez vous inscrire pour pouvoir vous connecter avec votre compte fédéré.";
    protected $code = 403;

    public function __construct(string $token, ?string $email, ?string $prenom, ?string $nom)
    {
        parent::__construct();
        $this->token = $token;
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

    public function getToken(): string
    {
        return $this->token;
    }

    public function getMessageb64(): string
    {
        return \base64_encode($this->message);
    }
}
