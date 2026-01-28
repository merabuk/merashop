<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\Exceptions\CreateRefreshTokenException;
use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\TokenHasherInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Ulid;
use Random\RandomException;
use Symfony\Component\Clock\ClockInterface;

final readonly class RefreshTokenService
{
    public function __construct(
        private RefreshTokenWriteRepositoryInterface $writeRepository,
        private TokenHasherInterface $tokenHasher,
        private ClockInterface $clock,
        private int $ttl,
    ) {
    }

    /**
     * @throws CreateRefreshTokenException
     */
    public function create(string $accountUlid, AccountTypeEnum $accountType): RefreshTokenData
    {
        try {
            $plainToken = $this->generatePlainToken();
            $hashedToken = $this->tokenHasher->hash($plainToken);

            $refreshToken = RefreshToken::create(
                token: TokenHash::fromString($hashedToken),
                accountUlid: Ulid::fromString($accountUlid),
                accountType: AccountType::fromEnum($accountType),
                expiresAt: ExpiresAt::fromDate($this->clock->now()->modify("+{$this->ttl} seconds"))
            );

            $this->writeRepository->deleteAllPrevious($refreshToken);
            $this->writeRepository->save($refreshToken);

            return new RefreshTokenData(token: $plainToken, expiresIn: $this->ttl);
        } catch (\DateMalformedStringException|InvalidIdentityAccessValueObjectExceptionInterface|RandomException $e) {
            throw new CreateRefreshTokenException('Error while creating refresh token', previous: $e);
        }
    }

    public function revoke(RefreshToken $refreshToken): void
    {
        $this->writeRepository->deleteAllPrevious($refreshToken);
    }

    /**
     * @throws RandomException
     */
    private function generatePlainToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
