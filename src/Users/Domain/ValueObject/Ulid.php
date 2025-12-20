<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUlidException;
use App\Shared\Domain\Service\UlidService;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserUlidException;

final class Ulid implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $ulid;

    /**
     * @throws InvalidUserUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            $this->ulid = UlidService::validate($ulid);
        } catch (InvalidUlidException) {
            throw InvalidUserUlidException::becauseItIsNotAValidUlid($ulid);
        }
    }

    /**
     * @throws InvalidUserUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }

    /**
     * @throws InvalidUserUlidException
     */
    public static function generate(): self
    {
        return new self(UlidService::generate());
    }

    public function value(): string
    {
        return $this->ulid;
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
