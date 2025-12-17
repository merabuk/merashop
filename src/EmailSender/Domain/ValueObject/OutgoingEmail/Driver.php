<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutgoingEmail;

use App\EmailSender\Domain\Enum\OutgoingEmail\EmailDriverEnum;
use App\EmailSender\Domain\Exception\OutgoingEmail\InvalidOutgoingEmailDriverException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Driver implements \Stringable
{
    use ValueObjectEqualityTrait;

    private EmailDriverEnum $driver;

    /**
     * @throws InvalidOutgoingEmailDriverException
     */
    public function __construct(string $status)
    {
        $enum = EmailDriverEnum::tryFrom($status);

        if (false === $enum instanceof EmailDriverEnum) {
            throw InvalidOutgoingEmailDriverException::becauseItIsNotAValidDriver(invalidValue: $status, availableValues: EmailDriverEnum::getValues());
        }

        $this->driver = $enum;
    }

    public function value(): EmailDriverEnum
    {
        return $this->driver;
    }

    /**
     * @throws InvalidOutgoingEmailDriverException
     */
    public static function fromString(string $driver): self
    {
        return new self($driver);
    }

    public function __toString(): string
    {
        return $this->value()->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value()->value;
    }
}
