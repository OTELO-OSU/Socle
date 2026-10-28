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
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $authService, private readonly ParameterBagInterface $params, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator, private readonly TranslatorInterface $translator) {}

    #[Route('/connexion', methods: ['POST'])]
    public function connexion(
        Request $request,
        #[Target('login')]
        RateLimiterFactoryInterface $loginLimiter
    ): Response {
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) return $this->json(['message' => $this->translator->trans('exception.auth.header_authorization_manquant')], 400);

        if (strpos($authorizationHeader, 'Basic ') !== 0) return $this->json(['message' => $this->translator->trans('exception.auth.header_authorization_invalide')], 400);

        $base64Credentials = substr($authorizationHeader, 6);
        $decodedCredentials = base64_decode($base64Credentials);

        if (!\str_contains($decodedCredentials, ":")) return $this->json(['message' => $this->translator->trans('exception.auth.header_authorization_invalide')], 400);

        // Récupération du username et du password
        list($username, $password) = explode(':', $decodedCredentials, 2);

        // On créé le limiter pour l'IP du client et on consomme une tentative après les tests de format
        $limiter = $loginLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => $this->translator->trans('exception.rate_limit.tentative_connexion')], 429);
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
        if (!$request->headers->has('mail') || !$request->headers->has('givenname') || !$request->headers->has('sn')) {
            return new RedirectResponse($this->params->get('front_host') . "/callbackCas?status=error");
        }
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
            return $this->json(['message' => $this->translator->trans('exception.rate_limit.tentative_inscription')], 429);
        }

        if ($ssmdp) {
            $emailValide = $this->authService->validationInscriptionOAuth($dto);
            if ($emailValide) {
                return $this->json(['message' => $this->translator->trans('auth.succes.inscription_reussie_attente_approbation')], 201);
            }
        } else {
            $this->authService->inscriptionBasic($dto);
        }

        return $this->json(['message' => $this->translator->trans('auth.succes.inscription_reussie')], 201);
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
        if (!$refreshToken) return $this->json(['message' => $this->translator->trans('exception.bad_request.refresh_token_manquant')], 400);

        $couple = $this->authService->checkRefreshToken($refreshToken);
        $response = $this->json(['token' => $couple['token']], 200);
        $response->headers->setCookie($couple['cookie']);
        return $response;
    }

    #[Route('/verifie-mail', methods: ['POST'])]
    public function verifieMail(
        Request $request,
        #[Target('verifier_email')]
        RateLimiterFactoryInterface $verifieMailLimiter
    ): Response {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['token'])) return $this->json(['message' => $this->translator->trans('utilisateur.exception.token_obligatoire')], 400);

        $limiter = $verifieMailLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => $this->translator->trans('exception.rate_limit.tentative_verification')], 429);
        }

        $this->authService->verificationMailUtilisateur($json['token']);
        return $this->json(['message' => $this->translator->trans('auth.succes.email_verifie')], 200);
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
        #[Target('mdp_oublie')]
        RateLimiterFactoryInterface $motDePasseOublieLimiter
    ): Response {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['email'])) return $this->json(['message' => $this->translator->trans('auth.exception.email_obligatoire')], 400);

        $limiter = $motDePasseOublieLimiter->create($request->getClientIp());
        $consume = $limiter->consume(1);
        if (!$consume->isAccepted()) {
            return $this->json(['message' => $this->translator->trans('exception.rate_limit.tentative_mdp_oublie')], 429);
        }

        $this->authService->motDePasseOubliePhase1($json['email']);
        return $this->json(['message' => $this->translator->trans('auth.succes.envoie_email_reinitialisation_mdp')], 200);
    }

    #[Route('/mdp-oublie', methods: ['PATCH'])]
    public function motDePasseOubliePhase2(Request $request, ValidatorInterface $validator): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['token'])) return $this->json(['message' => $this->translator->trans('utilisateur.exception.token_obligatoire')], 400);
        if (!isset($json['mdp'])) return $this->json(['message' => $this->translator->trans('auth.exception.mdp_obligatoire')], 400);

        // Vérifier si le mot de passe est compromis
        $violations = $validator->validate($json['mdp'], [
            new NotCompromisedPassword(message: $this->translator->trans('auth.exception.mdp_compromise'))
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                return $this->json(['message' => $violation->getMessage()], 400);
            }
        }

        $this->authService->motDePasseOubliePhase2($json['token'], $json['mdp']);
        return $this->json(['message' => $this->translator->trans('auth.succes.reinitialisation_mdp_reussie')], 200);
    }
}
