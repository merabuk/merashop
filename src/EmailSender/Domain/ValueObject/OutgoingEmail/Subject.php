<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutgoingEmail;

use App\EmailSender\Domain\Exception\OutgoingEmail\InvalidOutgoingEmailSubjectException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Subject implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $subject;

    /**
     * @throws InvalidOutgoingEmailSubjectException
     */
    public function __construct(string $subject)
    {
        try {
            $this->subject = StringValidator::validate(value: $subject, maxLength: self::MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidOutgoingEmailSubjectException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->subject;
    }

    /**
     * @throws InvalidOutgoingEmailSubjectException
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
