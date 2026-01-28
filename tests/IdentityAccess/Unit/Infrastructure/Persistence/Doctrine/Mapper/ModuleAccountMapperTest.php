<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Id;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmModuleAccount;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\ModuleAccountMapper;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use PHPUnit\Framework\TestCase;

final class ModuleAccountMapperTest extends TestCase
{
    private ModuleAccountMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ModuleAccountMapper();
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testToDoctrineOrm(): void
    {
        $domain = $this->makeDomainEntity();

        $orm = $this->mapper->toDoctrineOrm($domain);

        self::assertSame($domain->getId()?->value(), $orm->id);
        self::assertSame($domain->getUlid()->value(), $orm->ulid);
        self::assertSame($domain->getClientId()->value(), $orm->clientId);
        self::assertSame($domain->getClientSecret()->value(), $orm->clientSecret);
        self::assertSame($domain->getScopes()->toStrings(), $orm->scopes);
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
        self::assertSame($orm->ulid, $domain->getUlid()->value());
        self::assertSame($orm->clientId, $domain->getClientId()->value());
        self::assertSame($orm->clientSecret, $domain->getClientSecret()->value());
        self::assertSame($orm->scopes, $domain->getScopes()->toStrings());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->makeDomainEntity();
        $orm = new OrmModuleAccount();
        $orm->scopes = ['old-scope'];

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertEquals($domain->getScopes()->toStrings(), $orm->scopes);
        // no editable fields
        self::assertNull($orm->id);
        self::assertNull($orm->ulid);
        self::assertNull($orm->clientId);
        self::assertNull($orm->clientSecret);
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
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmModuleAccount());
    }

    /**
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    private function makeDomainEntity(): ModuleAccount
    {
        $fakeId = 123;
        $fakeUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeClientId = 'test-client';
        $fakeClientSecret = 'test-secret'; // hash isn't needed for this test
        $fakeScopes = ['scope1', 'scope2'];

        return new ModuleAccount(
            ulid: Ulid::fromString($fakeUlid),
            clientId: ClientId::fromString($fakeClientId),
            clientSecret: ClientSecretHash::fromString($fakeClientSecret),
            scopes: ScopeCollection::fromStrings($fakeScopes),
            id: Id::fromInt($fakeId)
        );
    }

    private function makeOrmEntity(): OrmModuleAccount
    {
        $fakeId = 123;
        $fakeUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeClientId = 'test-client';
        $fakeClientSecret = 'test-secret'; // hash isn't needed for this test
        $fakeScopes = ['scope3'];

        $orm = new OrmModuleAccount();
        $orm->setId($fakeId);
        $orm->ulid = $fakeUlid;
        $orm->clientId = $fakeClientId;
        $orm->clientSecret = $fakeClientSecret;
        $orm->scopes = $fakeScopes;

        return $orm;
    }
}
