<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountIdException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final class Id implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidUserAccountIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidUserAccountIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidUserAccountIdException
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
