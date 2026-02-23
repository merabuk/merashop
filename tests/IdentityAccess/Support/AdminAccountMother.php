<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Service\AdminAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordChangedAt;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Faker\Generator;

final readonly class AdminAccountMother
{
    private const string DEFAULT_PASSWORD_HASH = '$2y$13$EfTxMGbVX8xxscagFCXKVOHsSKEelbmVsbGX1otIsWzGIZ1cG1uTa'; // password

    public function __construct(
        private AdminAccountFactoryInterface $adminAccountFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    /**
     * Static method for Unit-tests.
     *
     * @param array<string, mixed> $overrides
     *
     * @throws InvalidIdentityAccessValueObjectException
     * @throws DateMalformedStringException
     */
    public static function createWithData(array $overrides = []): AdminAccount
    {
        $status = StatusEnum::Active;
        if (isset($overrides['status']) && $overrides['status'] instanceof StatusEnum) {
            $status = $overrides['status'];
        }

        $passwordChangedAt = StatusEnum::Active === $status
            ? new DateTimeImmutable()->modify('-1 day')
            : null;
        if (isset($overrides['passwordChangedAt'])) {
            $passwordChangedAt = $overrides['passwordChangedAt'] instanceof DateTimeImmutable
                ? $overrides['passwordChangedAt']
                : null;
        }

        return new AdminAccount(
            ulid: Ulid::fromString($overrides['ulid'] ?? '01KHVRCA679BJ6PBXX5N3G6RR5'),
            email: EmailAddress::fromString($overrides['email'] ?? 'admin@example.com'),
            passwordHash: PasswordHash::fromString($overrides['passwordHash'] ?? self::DEFAULT_PASSWORD_HASH),
            roles: RoleCollection::fromStrings($overrides['roles'] ?? [RoleEnum::Admin->value]),
            status: Status::fromEnum($status),
            passwordChangedAt: $passwordChangedAt ? PasswordChangedAt::fromDateTime($passwordChangedAt) : null,
            id: Id::fromInt($overrides['id'] ?? 123),
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function create(array $overrides = []): AdminAccount
    {
        return $this->adminAccountFactory->createForTest(
            ulid: $overrides['ulid'] ?? $this->ulidGenerator->next(),
            email: $overrides['email'] ?? $this->faker->unique()->safeEmail(),
            passwordHash: $overrides['passwordHash'] ?? self::DEFAULT_PASSWORD_HASH,
            roles: $overrides['roles'] ?? [RoleEnum::Admin->value],
            status: $overrides['status'] ?? StatusEnum::Active,
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
