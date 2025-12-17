<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutgoingEmail;

use App\EmailSender\Domain\Exception\OutgoingEmail\InvalidOutgoingEmailFromNameException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class FromName implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $name;

    /**
     * @throws InvalidOutgoingEmailFromNameException
     */
    public function __construct(string $name)
    {
        try {
            $this->name = StringValidator::validate(value: $name, maxLength: self::MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidOutgoingEmailFromNameException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->name;
    }

    /**
     * @throws InvalidOutgoingEmailFromNameException
     */
    public static function fromString(string $subject): self
    {
        return new self($subject);
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
