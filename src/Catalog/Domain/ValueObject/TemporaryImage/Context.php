<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Enum\ImageContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageContextException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class Context implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 50;

    private ImageContextEnum $value;

    private function __construct(ImageContextEnum $context)
    {
        $this->value = $context;
    }

    public static function fromEnum(ImageContextEnum $context): self
    {
        return new self($context);
    }

    /**
     * @throws InvalidTemporaryImageContextException
     */
    public static function fromString(string $context): self
    {
        $enum = ImageContextEnum::tryFrom($context);

        if (null === $enum) {
            throw InvalidTemporaryImageContextException::becauseItIsNotAValidContext(invalidValue: $context, availableValues: ImageContextEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): ImageContextEnum
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
