<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Token;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Ulid;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<RefreshToken, OrmRefreshToken>
 */
class RefreshTokenMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmRefreshToken
    {
        $this->assertIsType(RefreshToken::class, $domain);

        /** @var RefreshToken $domain */
        $orm = new OrmRefreshToken();

        $orm->setId($domain->getId()?->value());
        $orm->token = $domain->getToken()->value();
        $orm->accountUlid = $domain->getAccountUlid()->value();
        $orm->expiresAt = $domain->getExpiresAt()->value();

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectException
     */
    public function fromDoctrineOrm(object $orm): RefreshToken
    {
        $this->assertIsType(OrmRefreshToken::class, $orm);

        /* @var OrmRefreshToken $orm */
        return new RefreshToken(
            token: Token::fromString($orm->token),
            accountUlid: Ulid::fromString($orm->accountUlid),
            expiresAt: new ExpiresAt($orm->expiresAt),
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(RefreshToken::class, $domain);
        $this->assertIsType(OrmRefreshToken::class, $orm);

        /* @var RefreshToken $domain */
        /* @var OrmRefreshToken $orm */
        $orm->token = $domain->getToken()->value();
        $orm->accountUlid = $domain->getAccountUlid()->value();
        $orm->expiresAt = $domain->getExpiresAt()->value();
    }
}
