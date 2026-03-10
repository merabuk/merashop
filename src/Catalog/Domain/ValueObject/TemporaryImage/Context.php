<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageContextException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class Context implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private ContextEnum $value;

    private function __construct(ContextEnum $context)
    {
        $this->value = $context;
    }

    public static function fromEnum(ContextEnum $context): self
    {
        return new self($context);
    }

    /**
     * @throws InvalidTemporaryImageContextException
     */
    public static function fromString(string $context): self
    {
        $enum = ContextEnum::tryFrom(mb_trim($context));

        if (null === $enum) {
            throw InvalidTemporaryImageContextException::becauseItIsNotAValidContext(invalidValue: $context, availableValues: ContextEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): ContextEnum
    {
        return $this->value;
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
