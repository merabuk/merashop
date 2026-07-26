<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Identity;

use App\Shared\Domain\Exception\Services\Identity\InvalidUlidException as BaseInvalidUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\Service\Validation\UlidValidator;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

readonly class Ulid implements ValueObjectInterface
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
