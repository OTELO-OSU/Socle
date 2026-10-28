<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrcidService
{
    public function __construct(
        private readonly HttpClientInterface $c,
        private readonly ParameterBagInterface $params
    ) {}

    /**
     * @throws UnauthorizedHttpException
     * @return string access token ORCID
     */
    function getTokenOrcid(string $code)
    {
        // Echange du code contre un token
        $response = $this->c->request('POST', 'https://' . $this->params->get("domaine_orcid") . '/oauth/token', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => [
                'client_id' => $this->params->get("orcid_id"),
                'client_secret' => $this->params->get("orcid_secret"),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->params->get("orcid_redirect_uri"),
            ],
        ]);

        if ($response->getStatusCode() == 401) throw new UnauthorizedHttpException('Bearer', "La requête vers ORCID n'a pas abboutie.");

        $data = $response->toArray();

        // Token
        return $data['access_token'];
    }

    function getProfileOrcid(string $accessToken)
    {
        // Utilisation du token pour récupérer nom, prénom et sub
        $response = $this->c->request('GET', 'https://' . $this->params->get("domaine_orcid") . '/oauth/userinfo', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $data = $response->toArray();

        // Sub
        $sub = $data['sub'];

        // Prénom
        $firstname = $data['given_name'];

        // Nom
        $name = $data['family_name'];

        $response = $this->c->request('GET', 'https://pub.' . $this->params->get("domaine_orcid") . '/v3.0/' . $sub . '/email', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $data = $response->toArray();

        $emailArray = $data["email"];
        $email = null;

        if (!empty($emailArray)) {
            $email = $emailArray[0]['email'];
        }

        return [
            "sub" => $sub,
            "firstname" => $firstname,
            "name" => $name,
            "email" => $email
        ];
    }
}
