<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountTypeException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class AccountType implements \Stringable
{
    use ValueObjectEqualityTrait;

    private AccountTypeEnum $type;

    private function __construct(AccountTypeEnum $type)
    {
        $this->type = $type;
    }

    public static function fromEnum(AccountTypeEnum $type): self
    {
        return new self($type);
    }

    public static function user(): self
    {
        return self::fromEnum(AccountTypeEnum::User);
    }

    public static function module(): self
    {
        return self::fromEnum(AccountTypeEnum::Module);
    }

    /**
     * @throws InvalidRefreshTokenAccountTypeException
     */
    public static function fromString(string $type): self
    {
        $enum = AccountTypeEnum::tryFrom($type);

        if (null === $enum) {
            throw InvalidRefreshTokenAccountTypeException::becauseItIsNotAValidAccountType(invalidValue: $type, availableValues: AccountTypeEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): AccountTypeEnum
    {
        return $this->type;
    }

    public function isUser(): bool
    {
        return AccountTypeEnum::User === $this->type;
    }

    public function isModule(): bool
    {
        return AccountTypeEnum::Module === $this->type;
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
