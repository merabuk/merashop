<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUlidException;
use App\Shared\Domain\Service\UlidValidator;

class Ulid implements \Stringable
{
    use ValueObjectEqualityTrait;

    protected string $ulid;

    /**
     * @throws InvalidUlidException
     */
    protected function __construct(string $ulid)
    {
        $this->ulid = UlidValidator::validate($ulid);
    }

    /**
     * @throws InvalidUlidException
     */
    public static function fromString(string $ulid): static
    {
        return new static($ulid);
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
