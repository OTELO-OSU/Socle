<?php

namespace App\Controller;

use App\DTO\UtilisateurInscriptionDTO;
use App\Exception\auth\UtilisateurNonAdmisException;
use App\Exception\auth\UtilisateurOAuthExistePasException;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $authService, private readonly ParameterBagInterface $params, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator) {}

    #[Route('/connexion', methods: ['POST'])]
    public function connexion(
        Request $request,
        #[Target('login')]
        RateLimiterFactoryInterface $loginLimiter
    ): Response {
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) return $this->json(['message' => 'Le header autorisation est requis !'], 401);

        if (strpos($authorizationHeader, 'Basic ') !== 0) return $this->json(['message' => 'Encodage du header autorisation invalide'], 401);

        $base64Credentials = substr($authorizationHeader, 6);
        $decodedCredentials = base64_decode($base64Credentials);

        if (!\str_contains($decodedCredentials, ":")) return $this->json(['message' => 'Encodage du header autorisation invalide'], 401);

        // Récupération du username et du password
        list($username, $password) = explode(':', $decodedCredentials, 2);

        // On créé le limiter pour l'IP du client et on consomme une tentative après les tests de format
        $limiter = $loginLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => 'Trop de tentatives de connexion. Veuillez réessayer plus tard.'], 429);
        }

        $couple = $this->authService->connexionBasic($username, $password);

        // Si la connexion est réussie, on reset le limiter pour l'IP du client
        $limiter->reset();

        $response = $this->json(['token' => $couple['token']]);
        $response->headers->setCookie($couple['cookie']);
        return $response;
    }


    #[Route('/connexionFede', methods: ['GET'])]
    public function connexionFede(Request $request): Response
    {
        try {
            $mail = $request->headers->get('mail');
            $prenom = $request->headers->get('givenname');
            $nom = $request->headers->get('sn');
            $token = $this->authService->connexionFede($mail, $prenom, $nom);
            $response = new RedirectResponse($this->params->get('front_host') . "/callbackCas?status=success");
            $response->headers->setCookie($token["cookie"]);
            return $response;
        } catch (UtilisateurNonAdmisException $e) {
            return new RedirectResponse($this->params->get('front_host') . "/callbackCas?status=nonAdmis");
        } catch (UtilisateurOAuthExistePasException $e) {
            return new RedirectResponse($this->params->get('front_host') . "/inscription?" . http_build_query([
                'ssmdp' => true,
                'token' => $e->getToken(),
                'payload' => $e->getPayload(),
                'message' => $e->getMessageb64()
            ]));
        }
    }

    #[Route('/inscription', methods: ['POST'])]
    public function inscription(
        Request $request,
        #[Target('inscription')]
        RateLimiterFactoryInterface $inscriptionLimiter
    ): Response {
        $ssmdp = $request->query->get('ssmdp', false);
        $dto = $this->serializer->deserialize($request->getContent(), UtilisateurInscriptionDTO::class, 'json');

        if ($ssmdp) {
            $dtoValide = $this->validator->validate($dto, null, ['inscription', 'oauth', 'affiliation']);
        } else {
            $dtoValide = $this->validator->validate($dto, null, ['inscription', 'basic', 'affiliation']);
        }


        if (count($dtoValide) > 0) {
            $messages = [];
            foreach ($dtoValide as $violation) {
                $messages[] = $violation->getMessage();
            }
            return $this->json(['messageArray' => $messages], 400);
        }

        $limiter = $inscriptionLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => 'Trop de tentatives d\'inscription. Veuillez réessayer plus tard.'], 429);
        }

        if ($ssmdp) {
            $emailValide = $this->authService->validationInscriptionOAuth($dto);
            if ($emailValide) {
                return $this->json(['message' => 'Inscription réussie. Un email vous sera envoyé quand votre compte sera activé.'], 201);
            }
        } else {
            $this->authService->inscriptionBasic($dto);
        }

        return $this->json(['message' => 'Inscription réussie. Un email de validation a été envoyé.'], 201);
    }

    #[Route('/orcid/connexion/{code}', methods: ['POST', 'OPTIONS'])]
    public function connexionOrcid(string $code): Response
    {
        try {
            $couple = $this->authService->connexionOrcid($code);
            $response = $this->json(['token' => $couple['token']]);
            $response->headers->setCookie($couple['cookie']);
            return $response;
        } catch (UtilisateurOAuthExistePasException $e) {
            return $this->json([
                'redirect' => $this->params->get('front_host') . "/inscription?" . http_build_query([
                    'ssmdp' => true,
                    'token' => $e->getToken(),
                    'payload' => $e->getPayload(),
                    'message' => $e->getMessageb64()
                ]),
            ], 200);
        }
    }


    #[Route('/refresh', methods: ['POST'])]
    public function refresh(Request $request): Response
    {
        $refreshToken = $request->cookies->get('refresh_token');
        if (!$refreshToken) return $this->json(['message' => 'Aucun refresh token n\'a été envoyé.'], 400);

        $couple = $this->authService->checkRefreshToken($refreshToken);
        $response = $this->json(['token' => $couple['token']], 200);
        $response->headers->setCookie($couple['cookie']);
        return $response;
    }

    #[Route('/verifie-mail', methods: ['POST'])]
    public function verifieMail(
        Request $request,
        #[Target('verifier-email')]
        RateLimiterFactoryInterface $verifieMailLimiter
    ): Response {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['token'])) return $this->json(['message' => 'Le token est requis.'], 400);

        $limiter = $verifieMailLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => 'Trop de tentatives de vérification. Veuillez réessayer plus tard.'], 429);
        }

        $this->authService->verificationMailUtilisateur($json['token']);
        return $this->json(['message' => 'Email vérifié avec succès.'], 200);
    }


    #[Route('/deconnexion', methods: ['POST'])]
    public function deconnexion(Request $request): Response
    {
        $response = new Response();
        $refreshToken = $request->cookies->get('refresh_token');
        if ($refreshToken) {
            $this->authService->deconnexion($refreshToken);
            $response->headers->clearCookie('refresh_token');
        }
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    #[Route('/mdp-oublie', methods: ['POST'])]
    public function motDePasseOublie(
        Request $request,
        #[Target('mdp-oubliee')]
        RateLimiterFactoryInterface $motDePasseOublieLimiter
    ): Response {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) return $this->json(['message' => 'L\'email est requis.'], 400);

        $limiter = $motDePasseOublieLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => 'Trop de tentatives de réinitialisation de mot de passe. Veuillez réessayer plus tard.'], 429);
        }

        $this->authService->motDePasseOubliePhase1($json['email']);
        return $this->json(['message' => 'Un email avec les instructions pour réinitialiser votre mot de passe a été envoyé.'], 200);
    }

    #[Route('/mdp-oublie', methods: ['PATCH'])]
    public function motDePasseOubliePhase2(Request $request, ValidatorInterface $validator): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['token'])) return $this->json(['message' => 'Le token est requis.'], 400);
        if (!isset($json['mdp'])) return $this->json(['message' => 'Le mot de passe est requis.'], 400);

        // Vérifier si le mot de passe est compromis
        $violations = $validator->validate($json['mdp'], [
            new NotCompromisedPassword(message: 'Le mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.')
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                return $this->json(['message' => $violation->getMessage()], 400);
            }
        }

        $this->authService->motDePasseOubliePhase2($json['token'], $json['mdp']);
        return $this->json(['message' => 'Votre mot de passe a été réinitialisé avec succès.'], 200);
    }
}
