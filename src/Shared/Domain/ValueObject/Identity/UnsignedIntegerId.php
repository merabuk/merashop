<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Identity;

use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\IdInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

abstract readonly class UnsignedIntegerId implements ValueObjectInterface, IdInterface
{
    use ValueObjectEqualityTrait;

    protected int $id;

    /**
     * @throws IntegerIsNotUnsignedException
     */
    protected function __construct(int $id)
    {
        $this->id = IntegerValidator::validateUnsigned($id);
    }

    public function value(): int
    {
        return $this->id;
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value();
    }
}
