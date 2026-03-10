<?php

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailToException;
use App\Shared\Domain\Exception\InvalidEmailAddressException;
use App\Shared\Domain\Service\EmailValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class To implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 256;

    private string $email;

    /**
     * @throws InvalidOutboxEmailToException
     */
    public function __construct(string $email)
    {
        try {
            $this->email = EmailValidator::validate(email: $email, maxLength: self::MAX_LENGTH);
        } catch (InvalidEmailAddressException $e) {
            throw InvalidOutboxEmailToException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->email;
    }

    /**
     * @throws InvalidOutboxEmailToException
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
