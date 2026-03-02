<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

/**
 * @mixin TestCase
 */
trait AppListenerTrait
{
    protected function makeRequestEvent(
        ?Request $request = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): RequestEvent {
        return new RequestEvent(
            kernel: $this->createMock(HttpKernelInterface::class),
            request: $request ?? new Request(),
            requestType: $requestType
        );
    }

    protected function makeResponseEvent(
        ?Request $request = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
        ?Response $response = null,
    ): ResponseEvent {
        return new ResponseEvent(
            kernel: $this->createMock(HttpKernelInterface::class),
            request: $request ?? new Request(),
            requestType: $requestType,
            response: $response ?? new Response()
        );
    }

    protected function makeExceptionEvent(
        ?Request $request = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
        ?Throwable $exception = null,
    ): ExceptionEvent {
        return new ExceptionEvent(
            kernel: $this->createMock(HttpKernelInterface::class),
            request: $request ?? new Request(),
            requestType: $requestType,
            e: $exception ?? new Exception()
        );
    }
}
