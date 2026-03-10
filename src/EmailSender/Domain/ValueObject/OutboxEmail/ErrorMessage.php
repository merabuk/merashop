<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final class ErrorMessage implements EquatableInterface, Stringable
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
            throw InvalidOutboxEmailErrorMessageException::becauseItEmpty();
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
