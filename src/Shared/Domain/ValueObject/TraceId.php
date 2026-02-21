<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUuidException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\UuidValidator;
use Stringable;

final readonly class TraceId implements Stringable
{
    use ValueObjectEqualityTrait;

    private string $value;

    /**
     * @throws InvalidTraceIdException
     */
    public function __construct(string $value)
    {
        try {
            $this->value = UuidValidator::validateV7(mb_trim($value));
        } catch (InvalidUuidException $e) {
            throw InvalidTraceIdException::fromBase($e);
        }
    }

    /**
     * @throws InvalidTraceIdException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value;
    }
}
