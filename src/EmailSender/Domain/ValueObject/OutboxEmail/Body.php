<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailBodyException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Body implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    private string $body;

    /**
     * @throws InvalidOutboxEmailBodyException
     */
    public function __construct(string $body)
    {
        $trimmed = mb_trim($body);
        if (empty($trimmed)) {
            throw InvalidOutboxEmailBodyException::becauseItEmpty();
        }
        $this->body = $trimmed;
    }

    public function value(): string
    {
        return $this->body;
    }

    /**
     * @throws InvalidOutboxEmailBodyException
     */
    public static function fromString(string $body): self
    {
        return new self($body);
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
