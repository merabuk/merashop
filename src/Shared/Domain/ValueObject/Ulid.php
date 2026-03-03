<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\Services\InvalidUlidException as BaseInvalidUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\Service\UlidValidator;
use Stringable;

class Ulid implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    protected string $ulid;

    /**
     * @throws InvalidUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            $this->ulid = UlidValidator::validate(mb_trim($ulid));
        } catch (BaseInvalidUlidException $e) {
            throw InvalidUlidException::fromBase($e);
        }
    }

    /**
     * @throws InvalidUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
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
