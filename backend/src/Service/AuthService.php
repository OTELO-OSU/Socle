<?php

namespace App\Service;

use App\DTO\UtilisateurInscriptionDTO;
use App\Entity\Entite;
use App\Entity\Plateforme;
use App\Entity\RefreshToken;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInscription;
use App\Entity\UtilisateurInscriptionOAuth;
use App\Entity\UtilisateurVerification;
use App\Exception\auth\EmailOuMotDePasseIncorrectException;
use App\Exception\auth\RefreshTokenInvalideOuExpireException;
use App\Exception\auth\UtilisateurMailNonValideException;
use App\Exception\auth\UtilisateurNonAdmisException;
use App\Exception\auth\UtilisateurOAuthExistePasException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $tokenManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly OrcidService $orcidService,
        private readonly MailerService $mailerService,
        private readonly ParameterBagInterface $params,
        private readonly TranslatorInterface $translator
    ) {}

    function connexionBasic(string $email, string $password)
    {
        $email = strtolower($email);
        $userRepo = $this->em->getRepository(Utilisateur::class);
        /** @var Utilisateur | null $user */
        $user = $userRepo->findOneBy(["email" => $email, "attenteApprobationDU" => false]);

        // Si l'email n'existe pas
        if (is_null($user)) throw new EmailOuMotDePasseIncorrectException();

        // Si le mot de passe n'est pas bon
        if (!$this->passwordHasher->isPasswordValid($user, $password)) throw new EmailOuMotDePasseIncorrectException();

        if (!$user->isEmailValide()) {
            $this->envoyerEmailVerification($user);
            throw new UtilisateurMailNonValideException();
        }
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);
    }

    function connexionFede(string $email, string $prenom, string $nom)
    {
        $email = strtolower($email);
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email, "attenteApprobationDU" => false]);

        if (!$user) $this->creationInscriptionOAuth($email, $prenom, $nom, null);

        if (!$user->isEmailValide()) {
            $this->envoyerEmailVerification($user);
            throw new UtilisateurMailNonValideException();
        }
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);
    }

    function connexionOrcid(string $code)
    {
        $accessToken = $this->orcidService->getTokenOrcid($code);
        $profile = $this->orcidService->getProfileOrcid($accessToken);
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(["sub_orcid" => $profile["sub"]]);

        $email = $profile["email"] ?? null;
        if ($email) $email = strtolower($email);
        if (!$user) $this->creationInscriptionOAuth($email, $profile["given_name"] ?? null, $profile["family_name"] ?? null, $profile["sub"]);

        if (!$user->isEmailValide()) {
            $this->envoyerEmailVerification($user);
            throw new UtilisateurMailNonValideException();
        }
        if (!$user->isAdmis()) throw new UtilisateurNonAdmisException();

        return $this->genererTokenCookieAvecRefreshToken($user);
    }

    function inscriptionBasic(UtilisateurInscriptionDTO $dto)
    {
        // Vérification que l'email n'est pas déjà utilisé
        $email = strtolower($dto->email);
        $existingUser = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
        if ($existingUser) throw new ConflictHttpException($this->translator->trans('exception.conflict.email_deja_utilise'));

        $user = new Utilisateur();
        $user->setEmail($email);
        $user->setPrenom($dto->prenom);
        $user->setNom($dto->nom);
        $user->setMdp($this->passwordHasher->hashPassword($user, $dto->mdp));

        // Vérifier que l'entité est une unité est qu'elle est valide
        /** @var Entite|null $unite */
        $unite = $this->em->getRepository(Entite::class)->find($dto->unite_id);
        if (!$unite || !$unite->isLaboratoire()) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found.unite'));
        }

        $user->setUnite($unite);

        $this->em->persist($user);
        $this->em->flush();

        $userInscription = new UtilisateurInscription();
        $userInscription->setUtilisateur($user);
        $userInscription->setPlateformeDemande($dto->plateforme_demande_id ? $this->em->getReference(Plateforme::class, $dto->plateforme_demande_id) : null);
        $userInscription->setDirecteurUniteDemande($dto->directeur_unite_demande === true ? true : false);
        $this->em->persist($userInscription);
        $this->em->flush();

        $this->envoyerEmailVerification($user);
    }

    /**
     * Créer un record pour stocker les informations provenant des différents IDP et donne un token à l'utilisateur
     * pour qu'il puisse réuiliser ces informations lors de son inscription.
     * @throws UtilisateurOAuthExistePasException Lance cette erreur à chaque fois pour que les controlleurs puissent l'intercepter et récupérer le token et le payload dans l'erreur.
     */
    function creationInscriptionOAuth(?string $email, ?string $prenom, ?string $nom, ?string $subOrcid)
    {
        //* Vérifier si l'email n'est pas déjà dans une procédure de création de compte OAuth
        $OAuthExistant = $this->em->getRepository(UtilisateurInscriptionOAuth::class)->findOneBy(["email" => $email]);
        if ($OAuthExistant) {
            // On supprime le record
            $this->em->remove($OAuthExistant);
        }

        // On créé un nouveau record avec les nouvelles données
        $newOAuth = new UtilisateurInscriptionOAuth();
        if ($email) $newOAuth->setEmail($email);
        if ($prenom) $newOAuth->setPrenom($prenom);
        if ($nom) $newOAuth->setNom($nom);
        if ($subOrcid) $newOAuth->setSubOrcid($subOrcid);

        $token = bin2hex(random_bytes(64));

        $newOAuth->setToken($token);
        $newOAuth->setExpiration(new DateTime('+1 hour'));
        $this->em->persist($newOAuth);
        $this->em->flush();

        throw new UtilisateurOAuthExistePasException($token, $email, $prenom, $nom);
    }

    function validationInscriptionOAuth(UtilisateurInscriptionDTO $dto)
    {
        // Récupérer les infos de la table d'inscription OAuth
        $inscriptionOAuth = $this->em->getRepository(UtilisateurInscriptionOAuth::class)->findOneBy(["token" => $dto->token]);
        if (!$inscriptionOAuth) throw new UnauthorizedHttpException('Bearer', "Token d'inscription OAuth invalide.");
        if ($inscriptionOAuth->getExpiration() < new DateTime()) {
            $this->em->remove($inscriptionOAuth);
            $this->em->flush();
            throw new UnauthorizedHttpException('Bearer', "Token d'inscription OAuth expiré.");
        }

        $user = new Utilisateur();

        if ($inscriptionOAuth->getEmail()) {
            // Vérifier si le mail n'est pas déjà utilisé
            $email = strtolower($inscriptionOAuth->getEmail());
            $existingUser = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
            if ($existingUser) throw new ConflictHttpException($this->translator->trans('exception.conflict.email_deja_utilise'));
            $user->setEmail($email);
            $user->setEmailValide(true);
        } else {
            // Vérifier si le mail n'est pas déjà utilisé
            $email = strtolower($dto->email);
            $existingUser = $this->em->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);
            if ($existingUser) throw new ConflictHttpException($this->translator->trans('exception.conflict.email_deja_utilise'));
            $user->setEmail($email);
        }

        if ($inscriptionOAuth->getPrenom()) $user->setPrenom($inscriptionOAuth->getPrenom());
        else $user->setPrenom($dto->prenom);

        if ($inscriptionOAuth->getNom()) $user->setNom($inscriptionOAuth->getNom());
        else $user->setNom($dto->nom);

        if ($inscriptionOAuth->getSubOrcid()) $user->setSubOrcid($inscriptionOAuth->getSubOrcid());

        // Vérifier que l'entité est une unité est qu'elle est valide
        /** @var Entite|null $unite */
        $unite = $this->em->getRepository(Entite::class)->find($dto->unite_id);
        if (!$unite || !$unite->isLaboratoire()) {
            throw new NotFoundHttpException($this->translator->trans('exception.not_found.unite'));
        }

        $user->setUnite($unite);

        $this->em->persist($user);
        $this->em->flush();

        $userInscription = new UtilisateurInscription();
        $userInscription->setUtilisateur($user);
        $userInscription->setPlateformeDemande($dto->plateforme_demande_id ? $this->em->getReference(Plateforme::class, $dto->plateforme_demande_id) : null);
        $userInscription->setDirecteurUniteDemande($dto->directeur_unite_demande === true ? true : false);

        $this->em->persist($userInscription);
        $this->em->remove($inscriptionOAuth);
        $this->em->flush();

        if (!$user->isEmailValide()) {
            $this->envoyerEmailVerification($user);
            return false;
        }

        return true;
    }

    function checkRefreshToken(string $refreshToken)
    {
        $refreshTokenHash = hash('sha256', $refreshToken);
        /** @var RefreshToken $refreshToken */
        // Récupération du record refreshToken
        $refreshToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $refreshTokenHash]);

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

    private function genererTokenCookieAvecRefreshToken(Utilisateur $user)
    {
        $locRoles = $user->getRoles();
        $du = $user->getEntites()->count();
        $duDelegue = $user->getDeleguesDirecteur()->count();
        if ($du > 0) {
            $locRoles[] = "ROLE_DIRECTEUR_UNITE";
        } else if ($duDelegue > 0) {
            $locRoles[] = "ROLE_DIRECTEUR_UNITE_DELEGUE";
        }
        $token = $this->tokenManager->createFromPayload($user, [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "prenom" => $user->getPrenom(),
            "nom" => $user->getNom(),
            "roles" => $locRoles
        ]);

        // Création d'un token
        $refreshToken = bin2hex(random_bytes(64));

        $refreshTokenHash = hash('sha256', $refreshToken);

        // Définition de l'expiration dans 7 jours
        $expiration = new \DateTime('+7 days');

        // Création du record de refreshToken
        $refreshTokenEntity = new RefreshToken();
        $refreshTokenEntity->setToken($refreshTokenHash);
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
            ->withSameSite('Lax')
            ->withPath('/');

        return [
            "token" => $token,
            "cookie" => $cookie
        ];
    }

    private function envoyerEmailVerification(Utilisateur $user)
    {
        $verif = $this->em->getRepository(UtilisateurVerification::class)
            ->findOneBy(['utilisateur' => $user]);

        if (!$verif) {
            $verif = new UtilisateurVerification();
            $verif->setUtilisateur($user);
        }

        // Générer un token de vérification
        $token = bin2hex(random_bytes(64));

        // Update existing token or create it if missing.
        $verif->setToken($token);
        $verif->setExpiration(new DateTime('+1 hour'));
        $verif->setType("email_validation");
        $this->em->persist($verif);
        $this->em->flush();

        // Envoyer l'email de vérification avec le token
        $verificationLink = $this->params->get('front_host') . "/verification-email?token=" . $token;
        $sujet = "Vérification de votre adresse email";
        $body = "Bonjour " . $user->getPrenom() . ",\n\nVeuillez cliquer sur le bouton pour vérifier votre adresse email.\n\nCe lien expirera dans 1 heure.";

        $this->mailerService->sendEmailToUser($user->getEmail(), $sujet, $body, $verificationLink);
    }

    public function verificationMailUtilisateur(string $token)
    {
        $verif = $this->em->getRepository(UtilisateurVerification::class)
            ->findOneBy(['token' => $token, 'type' => 'email_validation']);

        if (!$verif) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('exception.bad_request.token_invalide'));
        }

        if ($verif->getExpiration() < new DateTime()) {
            $this->em->remove($verif);
            $this->em->flush();
            throw new GoneHttpException($this->translator->trans('exception.gone.token_expire'));
        }

        $user = $verif->getUtilisateur();
        $user->setEmailValide(true);
        $this->em->persist($user);
        $this->em->remove($verif);
        $this->em->flush();

        $this->mailerService->sendEmailToUser(
            $user->getEmail(),
            "Votre adresse email a été vérifiée",
            "Bonjour " . $user->getPrenom() . ",\n\nVotre adresse email a été vérifiée avec succès. Un administrateur va maintenant devoir valider votre compte avant que vous puissiez vous connecter. Vous recevrez un email de confirmation une fois que ce sera fait.\n\nMerci de votre patience."
        );

        // Enovyer un email à tous les admins pour les prévenir de la validation de l'email et qu'ils doivent valider le compte
        // on find les admins sur le fait que le json roles contient "ROLE_ADMIN"
        $admins = $this->em->getRepository(Utilisateur::class)->findByRole("ROLE_ADMIN");
        foreach ($admins as $admin) {
            $sujet = "Nouvel utilisateur en attente d'approbation";
            $body = "Bonjour " . $admin->getPrenom() . ",\n\nL'utilisateur " . $user->getEmail() . " a vérifié son adresse email. Veuillez vous connecter à votre espace admin pour valider ou refuser son compte.";
            $this->mailerService->sendEmailToUser($admin->getEmail(), $sujet, $body, $this->params->get('front_host') . '/administration');
        }
    }

    function motDePasseOubliePhase1(string $email): void
    {
        $email = strtolower($email);
        /** @var Utilisateur $user */
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return;
        }

        if ($user->getUtilisateurVerification()) {
            $this->em->remove($user->getUtilisateurVerification());
            $this->em->flush();
            $user->setUtilisateurVerification(null);
        }

        $token = bin2hex(random_bytes(32));

        $userVerification = new UtilisateurVerification();
        $userVerification->setUtilisateur($user);
        $userVerification->setToken($token);
        $userVerification->setExpiration(new \DateTime('+1 hour'));
        $userVerification->setType("password_reset");

        $this->em->persist($userVerification);
        $this->em->flush();

        $host = $this->params->get('front_host');

        $resetLink = $host . "/reinitialisation-mdp?token=" . $token;

        $this->mailerService->sendEmailToUser(
            $user->getEmail(),
            "Réinitialisation de votre mot de passe",
            "Une réinitalisation de mot de passe a été demandée pour votre compte.\n\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.",
            $resetLink
        );
    }

    function motDePasseOubliePhase2(string $token, string $mdp): void
    {
        /** @var UtilisateurVerification $user */
        $user = $this->em->getRepository(UtilisateurVerification::class)->findOneBy(['token' => $token, 'type' => 'password_reset']);
        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('exception.bad_request.token_invalide'));
        }

        if ($user->getExpiration() < new \DateTime()) {
            throw new GoneHttpException($this->translator->trans('exception.gone.token_expire'));
        }

        $user->getUtilisateur()->setMdp($this->passwordHasher->hashPassword($user->getUtilisateur(), $mdp));
        $this->em->remove($user);
        $this->em->flush();
    }
}
