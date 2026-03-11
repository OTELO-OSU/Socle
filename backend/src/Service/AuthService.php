<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\Utilisateur;
use App\Exception\auth\EmailOuMotDePasseIncorrectException;
use App\Exception\auth\RefreshTokenInvalideOuExpireException;
use App\Exception\auth\UtilisateurFedeExistePasException;
use App\Exception\auth\UtilisateurMailNonValideException;
use App\Exception\auth\UtilisateurNonAdmisException;
use App\Exception\auth\UtilisateurOrcidExistePasException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $tokenManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly OrcidService $orcidService
    ) {}

    function connexionBasic(string $email, string $password)
    {
        $userRepo = $this->em->getRepository(Utilisateur::class);
        /** @var Utilisateur | null $user */
        $user = $userRepo->findOneBy(["email" => $email]);

        // Si l'email n'existe pas
        if (is_null($user)) throw new EmailOuMotDePasseIncorrectException();

        // Si le mot de passe n'est pas bon
        if (!$this->passwordHasher->isPasswordValid($user, $password)) throw new EmailOuMotDePasseIncorrectException();

        if (!$user->isEmailValide()) throw new UtilisateurMailNonValideException();
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);
    }

    function connexionFede(string $email, string $prenom, string $nom)
    {
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
        if (!$user) throw new UtilisateurFedeExistePasException($email, $prenom, $nom);
        if (!$user->isEmailValide()) throw new UtilisateurMailNonValideException();
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);

        return [
            "token" => $token,
            "cookie" => $cookie
        ];
    }

    function connexionOrcid(string $code)
    {
        $accessToken = $this->orcidService->getTokenOrcid($code);
        $profile = $this->orcidService->getProfileOrcid($accessToken);
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["sub_orcid" => $profile["sub"]]);

        if (!$user) throw new UtilisateurOrcidExistePasException();
        if (!$user->isEmailValide()) throw new UtilisateurMailNonValideException();
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);
    }


    function checkRefreshToken(string $refreshToken)
    {
        /** @var RefreshToken $refreshToken */
        // Récupération du record refreshToken
        $refreshToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $refreshToken]);

        // Vérification que le refreshToken soit existant et pas expiré
        if (!$refreshToken || $refreshToken->getExpiration() < new \DateTime()) throw new RefreshTokenInvalideOuExpireException();

        // Suppression de l'ancien refreshToken
        $this->em->remove($refreshToken);
        $this->em->flush();

        return $this->genererTokenCookieAvecRefreshToken($refreshToken->getUtilisateur());
    }

    function deconnexion(string $refreshToken): void
    {
        $refreshTokenEntity = $this->em->getRepository(RefreshToken::class)->findOneBy(["token" => $refreshToken]);
        if ($refreshTokenEntity) {
            $this->em->remove($refreshTokenEntity);
            $this->em->flush();
        }
    }

    /**
     * @return Cookie
     */
    private function genererTokenCookieAvecRefreshToken(Utilisateur $user)
    {
        $token = $this->tokenManager->create($user);

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
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('None')
            ->withPath('/');

        return [
            "token" => $token,
            "cookie" => $cookie
        ];
    }
}
