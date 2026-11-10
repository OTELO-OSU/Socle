<?php

namespace App\EventListener;

use App\Exception\auth\RefreshTokenInvalideOuExpireException;
use App\Exception\auth\UtilisateurMailNonValideException;
use App\Exception\auth\UtilisateurNonAdmisException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof UtilisateurMailNonValideException) {
            $event->setResponse(
                new JsonResponse([
                    'message' => $this->translator->trans('auth.exception.email_non_verifie'),
                ], 401)
            );
        } else if ($e instanceof UtilisateurNonAdmisException) {
            $event->setResponse(
                new JsonResponse([
                    'message' => $this->translator->trans('auth.exception.compte_non_approuve'),
                ], 401)
            );
        } else if ($e instanceof RefreshTokenInvalideOuExpireException) {
            $event->setResponse(
                new JsonResponse([
                    'message' => $this->translator->trans('auth.exception.refresh_token_invalide_ou_expire'),
                ], 401)
            );
        } else if ($e instanceof HttpExceptionInterface) {
            $event->setResponse(
                new JsonResponse([
                    'message' => $e->getMessage(),
                ], $e->getStatusCode())
            );
        } else {
            \dd($e);
            $event->setResponse(
                new JsonResponse([
                    'message' => 'Une erreur est survenue.',
                ], 500)
            );
        }
    }
}
