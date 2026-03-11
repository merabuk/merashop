<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Factory\Contract\AdminAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordChangedAt;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use DateTimeImmutable;
use Faker\Generator;

final readonly class AdminAccountMother
{
    public const string DEFAULT_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';
    public const string DEFAULT_EMAIL = 'admin@example.com';
    private const string DEFAULT_PASSWORD_HASH = '$2y$13$EfTxMGbVX8xxscagFCXKVOHsSKEelbmVsbGX1otIsWzGIZ1cG1uTa'; // password

    public function __construct(
        private AdminAccountFactoryInterface $adminAccountFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    /**
     * @param ?string[] $roles
     *
     * @throws InvalidIdentityAccessValueObjectException
     */
    public static function createWithData(
        ?string $ulid = null,
        ?string $email = null,
        ?string $passwordHash = null,
        ?array $roles = null,
        StatusEnum $status = StatusEnum::Active,
        ?DateTimeImmutable $passwordChangedAt = null,
        ?int $id = null,
    ): AdminAccount {
        return new AdminAccount(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            email: EmailAddress::fromString($email ?? self::DEFAULT_EMAIL),
            passwordHash: PasswordHash::fromString($passwordHash ?? self::DEFAULT_PASSWORD_HASH),
            roles: RoleCollection::fromStrings($roles ?? [RoleEnum::Admin->value]),
            status: Status::fromEnum($status),
            passwordChangedAt: $passwordChangedAt ? PasswordChangedAt::fromDateTime($passwordChangedAt) : null,
            id: $id ? Id::fromInt($id) : null
        );
    }

    /**
     * @param ?string[] $roles
     */
    public function create(
        ?string $ulid = null,
        ?string $email = null,
        ?string $passwordHash = null,
        ?array $roles = null,
        StatusEnum $status = StatusEnum::Active,
    ): AdminAccount {
        return $this->adminAccountFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            email: $email ?? $this->faker->unique()->safeEmail(),
            passwordHash: $passwordHash ?? self::DEFAULT_PASSWORD_HASH,
            roles: $roles ?? [RoleEnum::Admin->value],
            status: $status,
        );
    }

    /**
     * @return AdminAccount[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }
}
