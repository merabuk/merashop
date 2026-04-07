<?php

namespace App\Shared\Domain\ValueObject\Temporal;

use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

abstract readonly class BaseVersionValueObject implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private int $value;

    /**
     * @throws IntegerIsNotUnsignedException
     */
    public function __construct(int $value)
    {
        $this->value = IntegerValidator::validateUnsigned($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    protected static function getInitialValue(): int
    {
        return 1;
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value;
    }
}
