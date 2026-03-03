<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class Subject implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $subject;

    /**
     * @throws InvalidOutboxEmailSubjectException
     */
    public function __construct(string $subject)
    {
        try {
            $this->subject = StringValidator::validate(value: $subject, maxLength: self::MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidOutboxEmailSubjectException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->subject;
    }

    /**
     * @throws InvalidOutboxEmailSubjectException
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
