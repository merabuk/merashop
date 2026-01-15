<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class Token implements \Stringable
{
    use ValueObjectEqualityTrait;

    private string $token;

    /**
     * @throws InvalidRefreshTokenTokenException
     */
    public function __construct(string $token)
    {
        if (empty($token)) {
            throw InvalidRefreshTokenTokenException::fromEmptyToken();
        }

        $this->token = $token;
    }

    public function value(): string
    {
        return $this->token;
    }

    /**
     * @throws InvalidRefreshTokenTokenException
     */
    public static function fromString(string $token): self
    {
        return new self($token);
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
