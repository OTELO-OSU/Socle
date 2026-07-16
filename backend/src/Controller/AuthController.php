<?php

namespace App\Controller;

use App\DTO\UtilisateurInscriptionDTO;
use App\Exception\auth\UtilisateurFedeExistePasException;
use App\Exception\auth\UtilisateurNonAdmisException;
use App\Exception\auth\UtilisateurOAuthExistePasException;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $authService, private readonly ParameterBagInterface $params, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator) {}

    #[Route('/connexion', methods: ['POST'])]
    public function connexion(Request $request): Response
    {
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) return $this->json(['message' => 'Le header autorisation est requis !'], 401);

        if (strpos($authorizationHeader, 'Basic ') !== 0) return $this->json(['message' => 'Encodage du header autorisation invalide'], 401);

        $base64Credentials = substr($authorizationHeader, 6);
        $decodedCredentials = base64_decode($base64Credentials);

        if (!\str_contains($decodedCredentials, ":")) return $this->json(['message' => 'Encodage du header autorisation invalide'], 401);

        // Récupération du username et du password
        list($username, $password) = explode(':', $decodedCredentials);

        $couple = $this->authService->connexionBasic($username, $password);

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
    public function inscription(Request $request): Response
    {
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
    public function verifieMail(Request $request): Response
    {
        $json = json_decode($request->getContent(), true);
        if (!isset($json['token'])) return $this->json(['message' => 'Le token est requis.'], 400);


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
}
