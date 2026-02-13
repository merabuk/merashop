<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use DateTimeImmutable;
use Stringable;

final class PasswordChangedAt implements Stringable
{
    use ValueObjectEqualityTrait;

    private DateTimeImmutable $date;

    public function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    public function value(): DateTimeImmutable
    {
        return $this->date;
    }

    public function __toString(): string
    {
        return $this->getPrimitiveValue();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value()->format(DateTimeImmutable::ATOM);
    }
}
