<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Attempts implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int DEFAULT = 0;

    private int $value;

    /**
     * @throws InvalidOutboxEmailAttemptsException
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvalidOutboxEmailAttemptsException('Attempts count cannot be negative');
        }
        $this->value = $value;
    }

    public static function initialize(): self
    {
        return new self(self::DEFAULT);
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     */
    public function increment(): self
    {
        return new self($this->value + 1);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function hasExceeded(int $maxAttempts): bool
    {
        return $this->value >= $maxAttempts;
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value;
    }
}
