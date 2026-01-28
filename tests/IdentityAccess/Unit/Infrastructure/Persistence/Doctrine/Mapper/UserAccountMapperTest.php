<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Id;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\UserAccountMapper;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use PHPUnit\Framework\TestCase;

final class UserAccountMapperTest extends TestCase
{
    private UserAccountMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new UserAccountMapper();
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
        self::assertSame($domain->getEmail()->value(), $orm->email);
        self::assertSame($domain->getPasswordHash()->value(), $orm->passwordHash);
        self::assertSame($domain->getRoles()->toStrings(), $orm->roles);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testFromDoctrineOrm(): void
    {
        $orm = $this->makeOrmEntity();

        $domain = $this->mapper->fromDoctrineOrm($orm);

        self::assertSame($orm->id, $domain->getId()?->value());
        self::assertSame($orm->ulid, $domain->getUlid()->value());
        self::assertSame($orm->email, $domain->getEmail()->value());
        self::assertSame($orm->passwordHash, $domain->getPasswordHash()->value());
        self::assertSame($orm->roles, $domain->getRoles()->toStrings());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->makeDomainEntity();
        $orm = new OrmUserAccount();
        $orm->roles = ['ROLE_OLD'];

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertEquals($domain->getRoles()->toStrings(), $orm->roles);
        // no editable fields
        self::assertNull($orm->id);
        self::assertNull($orm->ulid);
        self::assertNull($orm->email);
        self::assertNull($orm->passwordHash);
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
        $this->mapper->fromDoctrineOrm(new OrmUserAccount());
    }

    /**
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     */
    private function makeDomainEntity(): UserAccount
    {
        $fakeId = 123;
        $fakeUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeEmail = 'test@example.com';
        $fakePassword = 'test-password'; // hash isn't needed for this test
        $fakeRoles = ['ROLE_USER'];

        return new UserAccount(
            ulid: Ulid::fromString($fakeUlid),
            email: EmailAddress::fromString($fakeEmail),
            passwordHash: PasswordHash::fromString($fakePassword),
            roles: RoleCollection::fromStrings($fakeRoles),
            id: Id::fromInt($fakeId)
        );
    }

    private function makeOrmEntity(): OrmUserAccount
    {
        $fakeId = 123;
        $fakeUlid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $fakeEmail = 'test@example.com';
        $fakePassword = 'test-password'; // hash isn't needed for this test'
        $fakeRoles = ['ROLE_USER'];

        $orm = new OrmUserAccount();
        $orm->setId($fakeId);
        $orm->ulid = $fakeUlid;
        $orm->email = $fakeEmail;
        $orm->passwordHash = $fakePassword;
        $orm->roles = $fakeRoles;

        return $orm;
    }
}
