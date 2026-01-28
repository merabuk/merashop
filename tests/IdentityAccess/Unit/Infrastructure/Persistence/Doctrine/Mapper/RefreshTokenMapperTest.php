<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountUlidException;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Ulid;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\RefreshTokenMapper;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use PHPUnit\Framework\TestCase;

final class RefreshTokenMapperTest extends TestCase
{
    private RefreshTokenMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RefreshTokenMapper();
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     * @throws InvalidModuleAccountUlidException
     * @throws InvalidRefreshTokenTokenHashException
     */
    public function testToDoctrineOrm(): void
    {
        $domain = $this->makeDomainEntity();

        $orm = $this->mapper->toDoctrineOrm($domain);

        self::assertSame($domain->getId()?->value(), $orm->id);
        self::assertSame($domain->getTokenHash()->value(), $orm->token);
        self::assertSame($domain->getAccountUlid()->value(), $orm->accountUlid);
        self::assertSame($domain->getAccountType()->value(), $orm->accountType);
        self::assertSame($domain->getExpiresAt()->value(), $orm->expiresAt);
    }

    /**
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     */
    public function testFromDoctrineOrm(): void
    {
        $orm = $this->makeOrmEntity();

        $domain = $this->mapper->fromDoctrineOrm($orm);

        self::assertSame($orm->id, $domain->getId()?->value());
        self::assertSame($orm->token, $domain->getTokenHash()->value());
        self::assertSame($orm->accountUlid, $domain->getAccountUlid()->value());
        self::assertSame($orm->accountType, $domain->getAccountType()->value());
        self::assertSame($orm->expiresAt, $domain->getExpiresAt()->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->makeDomainEntity();
        $orm = new OrmRefreshToken();

        $this->mapper->mapToExistingOrm($domain, $orm);

        // no editable fields
        self::assertNull($orm->id);
        self::assertNull($orm->token);
        self::assertNull($orm->accountUlid);
        self::assertNull($orm->accountType);
        self::assertNull($orm->expiresAt);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testThrowExceptionOnInvalidEntity(): void
    {
        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->fromDoctrineOrm(new \stdClass());

        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->toDoctrineOrm(new \stdClass());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmRefreshToken());
    }

    /**
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    private function makeDomainEntity(): RefreshToken
    {
        $fakeId = 123;
        $fakeToken = 'test-token';
        $fakeAccountUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeExpiresAt = new \DateTimeImmutable('+30 days');

        return new RefreshToken(
            tokenHash: TokenHash::fromString($fakeToken),
            accountUlid: Ulid::fromString($fakeAccountUlid),
            accountType: AccountType::user(),
            expiresAt: ExpiresAt::fromDate($fakeExpiresAt),
            id: Id::fromInt($fakeId)
        );
    }

    private function makeOrmEntity(): OrmRefreshToken
    {
        $fakeId = 123;
        $fakeToken = 'test-token'; // hash isn't needed for this test'
        $fakeAccountUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeAccountType = AccountType::user()->value();
        $fakeExpiresAt = new \DateTimeImmutable('+30 days');

        $orm = new OrmRefreshToken();
        $orm->setId($fakeId);
        $orm->token = $fakeToken;
        $orm->accountUlid = $fakeAccountUlid;
        $orm->accountType = $fakeAccountType;
        $orm->expiresAt = $fakeExpiresAt;

        return $orm;
    }
}
