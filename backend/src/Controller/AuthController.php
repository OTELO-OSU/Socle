<?php

namespace App\Controller;

use App\Exception\auth\UtilisateurFedeExistePasException;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $authService, private readonly ParameterBagInterface $params) {}

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
        } catch (UtilisateurFedeExistePasException $e) {
            return new RedirectResponse($this->params->get('front_host') . "/inscription?" . http_build_query([
                'ssmdp' => true,
                'payload' => $e->getPayload(),
            ]));
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
