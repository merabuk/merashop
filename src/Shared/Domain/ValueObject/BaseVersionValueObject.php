<?php

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\IntegerValidator;
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
