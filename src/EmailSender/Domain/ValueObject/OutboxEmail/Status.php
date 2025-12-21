<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailStatusException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Status implements \Stringable
{
    use ValueObjectEqualityTrait;

    private StatusEnum $status;

    private function __construct(StatusEnum $status)
    {
        $this->status = $status;
    }

    public static function fromEnum(StatusEnum $status): self
    {
        return new self($status);
    }

    /**
     * @throws InvalidOutboxEmailStatusException
     */
    public static function fromString(string $status): self
    {
        $enum = StatusEnum::tryFrom($status);

        if (null === $enum) {
            throw InvalidOutboxEmailStatusException::becauseItIsNotAValidStatus(invalidValue: $status, availableValues: StatusEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): StatusEnum
    {
        return $this->status;
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
