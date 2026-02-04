<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountTypeException;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class AccountType implements Stringable
{
    use ValueObjectEqualityTrait;

    private IdentityTypeEnum $type;

    private function __construct(IdentityTypeEnum $type)
    {
        $this->type = $type;
    }

    public static function fromEnum(IdentityTypeEnum $type): self
    {
        return new self($type);
    }

    public static function user(): self
    {
        return self::fromEnum(IdentityTypeEnum::User);
    }

    public static function module(): self
    {
        return self::fromEnum(IdentityTypeEnum::Module);
    }

    /**
     * @throws InvalidRefreshTokenAccountTypeException
     */
    public static function fromString(string $type): self
    {
        $enum = IdentityTypeEnum::tryFrom($type);

        if (null === $enum) {
            throw InvalidRefreshTokenAccountTypeException::becauseItIsNotAValidAccountType(invalidValue: $type, availableValues: IdentityTypeEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): IdentityTypeEnum
    {
        return $this->type;
    }

    public function isUser(): bool
    {
        return IdentityTypeEnum::User === $this->type;
    }

    public function isModule(): bool
    {
        return IdentityTypeEnum::Module === $this->type;
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
