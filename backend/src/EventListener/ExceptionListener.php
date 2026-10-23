<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof HttpExceptionInterface) {
            $event->setResponse(
                new JsonResponse([
                    'message' => $e->getMessage(),
                ], $e->getStatusCode())
            );
        } else {
            $event->setResponse(
                new JsonResponse([
                    'message' => 'Une erreur est survenue.',
                ], 500)
            );
        }
    }
}
