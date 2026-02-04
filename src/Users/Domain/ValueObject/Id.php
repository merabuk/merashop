<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\Exception\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\IntegerValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserIdException;
use Stringable;

final class Id implements Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidUserIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidUserIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidUserIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value();
    }
}
