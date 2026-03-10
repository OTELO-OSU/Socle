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
    public function loginCAS(Request $request): Response
    {
        try {
            $mail = $request->headers->get('mail');
            $prenom = $request->headers->get('givenname');
            $nom = $request->headers->get('sn');
            $token = $this->authService->loginByCas($mail, $prenom, $nom);
            $response = new RedirectResponse($this->params->get('frontend_link') . "/callbackCas?status=success");
            $response->headers->setCookie($token["cookie"]);
            return $response;
        } catch (UtilisateurFedeExistePasException $e) {
            return new RedirectResponse($this->params->get('frontend_link') . "/inscription?ssmdp=true&payload=" . $e->getPayload());
        }
    }
}
