<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class ExpiresAt
{
    use ValueObjectEqualityTrait;

    public function __construct(private \DateTimeImmutable $date)
    {
    }

    public static function fromDate(\DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public function value(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function isExpired(): bool
    {
        return $this->date < new \DateTimeImmutable();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->date->format(\DateTimeInterface::ATOM);
    }
}
