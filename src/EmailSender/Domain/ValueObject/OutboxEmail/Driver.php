<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailDriverException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Driver implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    private DriverEnum $driver;

    public function __construct(DriverEnum $driver)
    {
        $this->driver = $driver;
    }

    public static function fromEnum(DriverEnum $driver): self
    {
        return new self($driver);
    }

    /**
     * @throws InvalidOutboxEmailDriverException
     */
    public static function fromString(string $driver): self
    {
        $enum = DriverEnum::tryFrom(mb_trim($driver));

        if (false === $enum instanceof DriverEnum) {
            throw InvalidOutboxEmailDriverException::becauseItIsNotAValidDriver(invalidValue: $driver, availableValues: DriverEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): DriverEnum
    {
        return $this->driver;
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
