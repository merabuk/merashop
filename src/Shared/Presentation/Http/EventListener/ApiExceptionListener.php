<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventListener;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\AppExceptionInterface;
use App\Shared\Domain\Exception\ConflictExceptionInterface;
use App\Shared\Domain\Exception\ForbiddenExceptionInterface;
use App\Shared\Domain\Exception\NotFoundExceptionInterface;
use App\Shared\Domain\Exception\ServerException;
use App\Shared\Domain\Exception\UnauthorizedExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiExceptionListener
{
    private const string API_PREFIX = '/api/';

    public function __construct(
        private readonly TranslatorInterface $translator,
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
        return match (true) {
            $exception instanceof AccessDeniedException => $this->handleAccessException($exception),
            $exception instanceof HttpExceptionInterface => $this->handleHttpException($exception),
            $exception instanceof HandlerFailedException => $this->handleHandlerFailedException($exception),
            $exception instanceof AppExceptionInterface => $this->handleAppException($exception),
            default => $this->logAndResponseWithBaseUnexpectedError($exception),
        };
    }

    private function handleAccessException(AccessDeniedException $exception): JsonResponse
    {
        $errorCode = ErrorCodeEnum::AccessDenied->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $this->translator->trans(
                id: $errorCode,
                domain: 'exceptions'.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            ),
            statusCode: Response::HTTP_FORBIDDEN,
        );
    }

    private function handleHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $previousException = $exception->getPrevious();

        if ($previousException instanceof ValidationFailedException) {
            return $this->handleValidationException($previousException);
        }

        if (
            $exception instanceof BadRequestHttpException
            && $previousException instanceof NotEncodableValueException
        ) {
            return $this->baseResponse(
                errorCode: ErrorCodeEnum::BadRequest->value,
                errorMessage: $exception->getMessage(),
                statusCode: Response::HTTP_BAD_REQUEST,
            );
        }

        // TODO: rework this on match

        $statusCode = $this->getStatusCode($exception);
        $errorCode = $previousException instanceof ServerException
            ? $previousException->getErrorCode()
            : ErrorCodeEnum::UnexpectedError->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $exception->getMessage(),
            statusCode: $statusCode,
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

        $errorCode = ErrorCodeEnum::ValidationFailed->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $this->translator->trans(
                id: $errorCode,
                parameters: [],
                domain: 'exceptions'.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            ),
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            extraData: ['violations' => $errors],
        );
    }

    public function handleHandlerFailedException(HandlerFailedException $exception): JsonResponse
    {
        $previousException = $exception->getPrevious();

        if ($previousException instanceof AppExceptionInterface) {
            return $this->handleAppException($previousException);
        }

        return $this->logAndResponseWithBaseUnexpectedError($exception);
    }

    public function handleAppException(AppExceptionInterface $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $errorCode = $exception->getErrorCode();
        $errorMessageData = $exception->getMessageData();

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $this->translator->trans(
                id: $errorCode,
                parameters: $errorMessageData,
                domain: 'exceptions'.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            ),
            statusCode: $statusCode
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

    private function logAndResponseWithBaseUnexpectedError(\Throwable $exception): JsonResponse
    {
        $this->logger->error($exception->getMessage(), [
            'exception_class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);

        $errorCode = ErrorCodeEnum::UnexpectedError->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $this->translator->trans(
                id: $errorCode,
                domain: 'exceptions'.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            ),
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    private function getStatusCode(\Throwable $exception): int
    {
        return match (true) {
            $exception instanceof UnauthorizedExceptionInterface => Response::HTTP_UNAUTHORIZED,
            $exception instanceof ForbiddenExceptionInterface => Response::HTTP_FORBIDDEN,
            $exception instanceof NotFoundExceptionInterface => Response::HTTP_NOT_FOUND,
            $exception instanceof ConflictExceptionInterface => Response::HTTP_CONFLICT,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }
}
