<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class TokenHash implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $tokenHash;

    /**
     * @throws InvalidRefreshTokenTokenHashException
     */
    public function __construct(string $hash)
    {
        $this->ensureIsValidHash($hash);

        $this->tokenHash = $hash;
    }

    public function value(): string
    {
        return $this->tokenHash;
    }

    /**
     * @throws InvalidRefreshTokenTokenHashException
     */
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    /**
     * @throws InvalidRefreshTokenTokenHashException
     */
    private function ensureIsValidHash(string $hash): void
    {
        if (empty($hash)) {
            throw InvalidRefreshTokenTokenHashException::fromEmptyTokenHash();
        }

        if (self::MAX_LENGTH < mb_strlen($hash)) {
            throw new InvalidRefreshTokenTokenHashException('Token hash is too long');
        }
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
