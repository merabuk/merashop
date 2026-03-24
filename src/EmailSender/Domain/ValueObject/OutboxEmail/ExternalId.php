<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class ExternalId implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 128;

    private string $externalId;

    /**
     * @throws InvalidOutboxEmailSubjectException
     */
    public function __construct(string $externalId)
    {
        try {
            $this->externalId = StringValidator::validate(rawValue: $externalId, maxLength: self::MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidOutboxEmailSubjectException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->externalId;
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
