<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutgoingEmail;

use App\EmailSender\Domain\Enum\OutgoingEmail\EmailStatusEnum;
use App\EmailSender\Domain\Exception\OutgoingEmail\InvalidOutgoingEmailStatusException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Status implements \Stringable
{
    use ValueObjectEqualityTrait;

    private EmailStatusEnum $status;

    /**
     * @throws InvalidOutgoingEmailStatusException
     */
    public function __construct(string $status)
    {
        $enum = EmailStatusEnum::tryFrom($status);

        if (false === $enum instanceof EmailStatusEnum) {
            throw InvalidOutgoingEmailStatusException::becauseItIsNotAValidStatus(invalidValue: $status, availableValues: EmailStatusEnum::getValues());
        }

        $this->status = $enum;
    }

    public function value(): EmailStatusEnum
    {
        return $this->status;
    }

    /**
     * @throws InvalidOutgoingEmailStatusException
     */
    public static function fromString(string $status): self
    {
        return new self($status);
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
