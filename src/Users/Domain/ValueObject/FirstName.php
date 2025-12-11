<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserFirstNameException;

final class FirstName implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $name;

    /**
     * @throws InvalidUserFirstNameException
     */
    public function __construct(string $name)
    {
        try {
            $this->name = StringValidator::validate(value: $name, maxLength: 60);
        } catch (InvalidStringException $e) {
            throw InvalidUserFirstNameException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->name;
    }

    /**
     * @throws InvalidUserFirstNameException
     */
    public static function fromString(string $name): self
    {
        return new self($name);
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
