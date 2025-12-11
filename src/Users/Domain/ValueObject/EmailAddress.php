<?php

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidEmailAddressException;
use App\Shared\Domain\Service\EmailValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserEmailAddressException;

final class EmailAddress implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $email;

    /**
     * @throws InvalidUserEmailAddressException
     */
    public function __construct(string $email)
    {
        try {
            $this->email = EmailValidator::validate($email);
        } catch (InvalidEmailAddressException $e) {
            throw InvalidUserEmailAddressException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->email;
    }

    /**
     * @throws InvalidUserEmailAddressException
     */
    public static function fromString(string $email): self
    {
        return new self($email);
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
