<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventListener;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\ServerException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionListener
{
    private const string API_PREFIX = '/api/';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!str_starts_with($request->getPathInfo(), self::API_PREFIX)) {
            return;
        }

        $response = $this->determineResponse($exception);

        $event->setResponse($response);
    }

    private function determineResponse(\Throwable $exception): JsonResponse
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $this->handleHttpException($exception);
        }

        // TODO: in future, handle specific exceptions using their own error codes

        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        return $this->baseResponse(
            errorCode: ErrorCodeEnum::UnexpectedError->value,
            errorMessage: 'An unexpected error occurred',
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    private function handleHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $previousException = $exception->getPrevious();

        if ($previousException instanceof ValidationFailedException) {
            return $this->handleValidationException($previousException);
        }

        $errorCode = $previousException instanceof ServerException
            ? $previousException->getErrorCode()
            : ErrorCodeEnum::UnexpectedError->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $exception->getMessage(),
            statusCode: $exception->getStatusCode(),
        );
    }

    private function handleValidationException(ValidationFailedException $validationException): JsonResponse
    {
        $violations = $validationException->getViolations();
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $this->baseResponse(
            errorCode: ErrorCodeEnum::ValidationFailed->value,
            errorMessage: 'Validation failed',
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            extraData: ['violations' => $errors],
        );
    }

    /**
     * @param array<string, mixed> $extraData
     */
    private function baseResponse(
        string $errorCode,
        string $errorMessage,
        int $statusCode,
        array $extraData = [],
    ): JsonResponse {
        return new JsonResponse([
            ...$extraData,
            'code' => $errorCode,
            'message' => $errorMessage,
        ], $statusCode);
    }
}
