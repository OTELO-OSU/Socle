<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\Utilisateur;
use App\Exception\auth\UtilisateurFedeExistePasException;
use App\Exception\auth\UtilisateurMailNonValideException;
use App\Exception\auth\UtilisateurNonAdmisException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $tokenManager,
    ) {}
    function loginByCas(string $email, string $prenom, string $nom)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
        if (!$user) throw new UtilisateurFedeExistePasException($email, $prenom, $nom);
        if (!$user->isEmailValide()) throw new UtilisateurMailNonValideException();
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        $token = $this->tokenManager->create($user);
        $cookie = $this->genererCookieAvecRefreshToken($user);

        return [
            "token" => $token,
            "cookie" => $cookie
        ];
    }

    /**
     * @return Cookie
     */
    function genererCookieAvecRefreshToken(Utilisateur $user)
    {
        // Création d'un token
        $refreshToken = bin2hex(random_bytes(64));

        // Définition de l'expiration dans 7 jours
        $expiration = new \DateTime('+7 days');

        // Création du record de refreshToken
        $refreshTokenEntity = new RefreshToken();
        $refreshTokenEntity->setToken($refreshToken);
        $refreshTokenEntity->setExpiration($expiration);
        $refreshTokenEntity->setUtilisateur($user);
        $this->em->persist($refreshTokenEntity);
        $this->em->flush();

        // Création du cookie de refreshToken
        $cookie = Cookie::create('refresh_token', $refreshToken)
            // Expiration dans 7 jours
            ->withExpires(time() + 604800)
            ->withHttpOnly(false)
            ->withSecure(true)
            ->withSameSite('None')
            ->withPath('/');

        return $cookie;
    }
}
