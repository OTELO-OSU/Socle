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

    #[Route('/connexionFede', methods: ['GET'])]
    public function connexionFede(Request $request): Response
    {
        try {
            $mail = $request->headers->get('mail');
            $prenom = $request->headers->get('givenname');
            $nom = $request->headers->get('sn');
            $token = $this->authService->connexionFede($mail, $prenom, $nom);
            $response = new RedirectResponse($this->params->get('frontend_link') . "/callbackCas?status=success");
            $response->headers->setCookie($token["cookie"]);
            return $response;
        } catch (UtilisateurFedeExistePasException $e) {
            return new RedirectResponse($this->params->get('frontend_link') . "/inscription?" . http_build_query([
                'ssmdp' => true,
                'payload' => $e->getPayload(),
            ]));
        }
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
