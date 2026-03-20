<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        } elseif ($e instanceof SerializerExceptionInterface) {
            // JSON mal formé
            $status = 400;
        } else {
            $status = $this->sanitizeStatus($e->getCode());
        }

        $event->setResponse(
            new JsonResponse([
                'message' => $e->getMessage(),
            ], $status)
        );
    }

    private function sanitizeStatus($code): int
    {
        $code = (int) $code;
        return ($code >= 400 && $code <= 599) ? $code : 500;
    }
}
