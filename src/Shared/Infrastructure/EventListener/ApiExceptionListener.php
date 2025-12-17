<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionListener implements EventSubscriberInterface
{
    private const string API_PREFIX = '/api/';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!str_starts_with($request->getPathInfo(), self::API_PREFIX)) {
            return;
        }

        [$statusCode, $errorMessage] = $this->determineStatusAndMessage($exception);

        $response = new JsonResponse([
            'error' => [
                'code' => $statusCode,
                'message' => $errorMessage,
            ],
        ], $statusCode);

        $event->setResponse($response);
    }

    /**
     * @return array{int, string}
     */
    private function determineStatusAndMessage(\Throwable $exception): array
    {
        if ($exception instanceof HttpExceptionInterface) {
            return [$exception->getStatusCode(), $exception->getMessage()];
        }

        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        return [Response::HTTP_INTERNAL_SERVER_ERROR, 'An unexpected error occurred'];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }
}
