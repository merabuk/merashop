<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Exception\Product\InvalidProductStatusException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Status implements EquatableInterface, Stringable
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

    public static function draft(): self
    {
        return self::fromEnum(StatusEnum::Draft);
    }

    public static function active(): self
    {
        return self::fromEnum(StatusEnum::Active);
    }

    public static function archived(): self
    {
        return self::fromEnum(StatusEnum::Archived);
    }

    /**
     * @throws InvalidProductStatusException
     */
    public static function fromString(string $status): self
    {
        $enum = StatusEnum::tryFrom(mb_trim($status));

        if (null === $enum) {
            throw InvalidProductStatusException::becauseItIsNotAValidStatus(invalidValue: $status, availableValues: StatusEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): StatusEnum
    {
        return $this->status;
    }

    public function asString(): string
    {
        return $this->status->value;
    }

    public function isDraft(): bool
    {
        return StatusEnum::Draft === $this->status;
    }

    public function isActive(): bool
    {
        return StatusEnum::Active === $this->status;
    }

    public function isArchived(): bool
    {
        return StatusEnum::Archived === $this->status;
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->asString();
    }
}
