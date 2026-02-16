<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventListener;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\AppExceptionInterface;
use App\Shared\Domain\Exception\ConflictExceptionInterface;
use App\Shared\Domain\Exception\ForbiddenExceptionInterface;
use App\Shared\Domain\Exception\NotFoundExceptionInterface;
use App\Shared\Domain\Exception\UnauthorizedExceptionInterface;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class ApiExceptionListener
{
    private const string PUBLIC_API_PREFIX = '/api/';
    private const string ADMIN_API_PREFIX = '/admin/api/';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TranslationDomainResolverInterface $translationDomainResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!$this->isApiRequest($request->getPathInfo())) {
            return;
        }

        $response = $this->determineResponse($exception);

        $event->setResponse($response);
    }

    private function isApiRequest(string $path): bool
    {
        return str_starts_with($path, self::PUBLIC_API_PREFIX)
            || str_starts_with($path, self::ADMIN_API_PREFIX);
    }

    private function determineResponse(Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof AppExceptionInterface => $this->handleAppException($exception),
            $exception instanceof AccessDeniedException => $this->handleAccessException($exception),
            $exception instanceof HttpExceptionInterface => $this->handleHttpException($exception),
            $exception instanceof HandlerFailedException => $this->handleHandlerFailedException($exception),
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
                domain: $this->translationDomainResolver->resolveIcuDomain('exceptions'),
            ),
            statusCode: Response::HTTP_FORBIDDEN,
        );
    }

    private function handleHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $previousException = $exception->getPrevious();

        return match (true) {
            $previousException instanceof ValidationFailedException => $this->handleValidationException($previousException),
            $exception instanceof BadRequestHttpException
            && $previousException instanceof NotEncodableValueException => $this->baseResponse(
                errorCode: ErrorCodeEnum::BadRequest->value,
                errorMessage: $exception->getMessage(),
                statusCode: Response::HTTP_BAD_REQUEST,
            ),
            $exception instanceof NotFoundHttpException
            && $previousException instanceof ResourceNotFoundException => $this->baseResponse(
                errorCode: ErrorCodeEnum::NotFound->value,
                errorMessage: $exception->getMessage(),
                statusCode: Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof UnsupportedMediaTypeHttpException => $this->baseResponse(
                errorCode: ErrorCodeEnum::UnsupportedMediaType->value,
                errorMessage: $exception->getMessage(),
                statusCode: Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
            ),
            default => $this->logAndResponseWithBaseUnexpectedError($exception),
        };
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
                domain: $this->translationDomainResolver->resolveIcuDomain('exceptions'),
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
                domain: $this->translationDomainResolver->resolveIcuDomain('exceptions'),
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

    private function logAndResponseWithBaseUnexpectedError(Throwable $exception): JsonResponse
    {
        dd([
            'exception' => $exception,
        ]);

        $this->logger->error($exception->getMessage(), [
            'exception_class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);

        $errorCode = ErrorCodeEnum::UnexpectedError->value;

        return $this->baseResponse(
            errorCode: $errorCode,
            errorMessage: $this->translator->trans(
                id: $errorCode,
                domain: $this->translationDomainResolver->resolveIcuDomain('exceptions'),
            ),
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    private function getStatusCode(Throwable $exception): int
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
