<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Request;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\Markers\BadRequestExceptionInterface;

class InvalidRequestHeaderValueException extends LogicException implements ClientFacingExceptionInterface, BadRequestExceptionInterface
{
    private string $requestName = 'unknown';

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::InvalidRequestHeaderValue->value;
    }

    public function withHeaderName(string $requestName): self
    {
        $this->requestName = $requestName;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return [
            'headerName' => $this->requestName,
        ];
    }
}
