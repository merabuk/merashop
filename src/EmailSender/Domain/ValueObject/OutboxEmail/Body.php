<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailBodyException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Body implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $body;

    /**
     * @throws InvalidOutboxEmailBodyException
     */
    public function __construct(string $body)
    {
        $trimmed = mb_trim($body);
        if ('' === $trimmed) {
            throw new InvalidOutboxEmailBodyException('Email body cannot be empty');
        }
        $this->body = $trimmed;
    }

    public function value(): string
    {
        return $this->body;
    }

    /**
     * @throws InvalidOutboxEmailBodyException
     */
    public static function fromString(string $body): self
    {
        return new self($body);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
