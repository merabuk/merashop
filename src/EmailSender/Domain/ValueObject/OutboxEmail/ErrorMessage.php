<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class ErrorMessage implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $message;

    /**
     * @throws InvalidOutboxEmailErrorMessageException
     */
    public function __construct(string $message)
    {
        $trimmed = mb_trim($message);
        if (empty($trimmed)) {
            throw new InvalidOutboxEmailErrorMessageException('Email error message cannot be empty');
        }

        $this->message = $trimmed;
    }

    public function value(): string
    {
        return $this->message;
    }

    /**
     * @throws InvalidOutboxEmailErrorMessageException
     */
    public static function fromString(string $subject): self
    {
        return new self($subject);
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
