<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountUlid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<RefreshToken, OrmRefreshToken>
 */
final readonly class RefreshTokenMapper implements MapperInterface
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
        $orm->token = $domain->getTokenHash()->value();
        $orm->accountUlid = $domain->getAccountUlid()->value();
        $orm->accountType = $domain->getAccountType()->value();
        $orm->expiresAt = $domain->getExpiresAt()->value();

        return $orm;
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectException
     */
    public function fromDoctrineOrm(object $orm): RefreshToken
    {
        $this->assertIsType(OrmRefreshToken::class, $orm);

        $id = Id::fromInt($orm->id ?? throw EntityFieldMissingException::forEntityId(className: $orm::class));
        /* @var OrmRefreshToken $orm */

        return new RefreshToken(
            tokenHash: TokenHash::fromString($orm->token ?? throw EntityFieldMissingException::forField(field: 'token', className: $orm::class)),
            accountUlid: AccountUlid::fromString($orm->accountUlid ?? throw EntityFieldMissingException::forField(field: 'accountUlid', className: $orm::class)),
            accountType: AccountType::fromEnum($orm->accountType ?? throw EntityFieldMissingException::forField(field: 'accountType', className: $orm::class)),
            expiresAt: ExpiresAt::fromDateTime($orm->expiresAt ?? throw EntityFieldMissingException::forField(field: 'expiresAt', className: $orm::class)),
            id: $id,
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
        // no editable fields
    }
}
