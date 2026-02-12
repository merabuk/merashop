<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountEmailException;
use App\Shared\Domain\Exception\InvalidEmailAddressException;
use App\Shared\Domain\Service\EmailValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class EmailAddress implements Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 256;

    private string $email;

    /**
     * @throws InvalidAdminAccountEmailException
     */
    public function __construct(string $email)
    {
        try {
            $this->email = EmailValidator::validate(email: $email, maxLength: self::MAX_LENGTH);
        } catch (InvalidEmailAddressException $e) {
            throw InvalidAdminAccountEmailException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->email;
    }

    /**
     * @throws InvalidAdminAccountEmailException
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
