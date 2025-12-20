<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailIdException;
use App\Shared\Domain\Exception\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\IntegerValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Id implements \Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidOutboxEmailIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidOutboxEmailIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidOutboxEmailIdException
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
