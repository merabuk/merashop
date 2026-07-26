<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionCodeException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Code implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 64;
    public const string REGEX = '/^(?![\d-])(?!.*--)[a-z\d-]+(?<!-)$/';

    private string $code;

    /**
     * @throws InvalidAttributeOptionCodeException
     */
    public function __construct(string $code)
    {
        $code = mb_trim($code);

        $this->ensureIsValidCode($code);

        $this->code = $code;
    }

    public function value(): string
    {
        return $this->code;
    }

    /**
     * @throws InvalidAttributeOptionCodeException
     */
    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }

    /**
     * @throws InvalidAttributeOptionCodeException
     */
    private function ensureIsValidCode(string $code): void
    {
        if ('' === $code) {
            throw InvalidAttributeOptionCodeException::becauseItIsEmpty();
        }
        if (mb_strlen($code) > self::MAX_LENGTH) {
            throw InvalidAttributeOptionCodeException::becauseItIsTooLong();
        }
        if (!preg_match(self::REGEX, $code)) {
            throw InvalidAttributeOptionCodeException::becauseItDoesNotMatchRegex();
        }
    }
}
