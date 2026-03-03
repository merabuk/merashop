<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class Scope implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private const string VALIDATION_PATTERN = '/^[a-z0-9]([a-z0-9._:-]*[a-z0-9])?$/';
    public const int MAX_LENGTH = 128;

    private string $value;

    /**
     * @throws InvalidScopeException
     */
    public function __construct(string $value)
    {
        $value = mb_strtolower(mb_trim($value));

        if (empty($value)) {
            throw InvalidScopeException::becauseItIsEmpty();
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw InvalidScopeException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        if (!preg_match(self::VALIDATION_PATTERN, $value)) {
            throw InvalidScopeException::becauseFormatIsInvalid($value);
        }

        $this->value = $value;
    }

    /**
     * @throws InvalidScopeException
     */
    public static function fromString(string $scope): self
    {
        return new self($scope);
    }

    public function value(): string
    {
        return $this->value;
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
