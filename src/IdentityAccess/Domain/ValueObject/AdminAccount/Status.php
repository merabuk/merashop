<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountStatusException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Status implements Stringable
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

    public static function active(): self
    {
        return self::fromEnum(StatusEnum::Active);
    }

    public static function inactive(): self
    {
        return self::fromEnum(StatusEnum::Inactive);
    }

    public static function draft(): self
    {
        return self::fromEnum(StatusEnum::Draft);
    }

    public static function blocked(): self
    {
        return self::fromEnum(StatusEnum::Blocked);
    }

    public static function deleted(): self
    {
        return self::fromEnum(StatusEnum::Deleted);
    }

    public static function vacation(): self
    {
        return self::fromEnum(StatusEnum::OnVacation);
    }

    /**
     * @throws InvalidAdminAccountStatusException
     */
    public static function fromString(string $status): self
    {
        $enum = StatusEnum::tryFrom(mb_trim($status));

        if (null === $enum) {
            throw InvalidAdminAccountStatusException::becauseItIsNotAValidStatus(invalidValue: $status, availableValues: StatusEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): StatusEnum
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return StatusEnum::Active === $this->status;
    }

    public function isInactive(): bool
    {
        return StatusEnum::Inactive === $this->status;
    }

    public function isDraft(): bool
    {
        return StatusEnum::Draft === $this->status;
    }

    public function isBlocked(): bool
    {
        return StatusEnum::Blocked === $this->status;
    }

    public function isDeleted(): bool
    {
        return StatusEnum::Deleted === $this->status;
    }

    public function isOnVacation(): bool
    {
        return StatusEnum::OnVacation === $this->status;
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
