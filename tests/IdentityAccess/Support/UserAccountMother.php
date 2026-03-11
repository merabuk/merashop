<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Factory\Contract\UserAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Id;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Generator;

final readonly class UserAccountMother
{
    public const string DEFAULT_ULID = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
    public const string DEFAULT_EMAIL = 'user@example.com';
    private const string DEFAULT_PASSWORD_HASH = '$2y$13$EfTxMGbVX8xxscagFCXKVOHsSKEelbmVsbGX1otIsWzGIZ1cG1uTa'; // password

    public function __construct(
        private UserAccountFactoryInterface $userAccountFactory,
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
        ?int $id = null,
    ): UserAccount {
        return new UserAccount(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            email: EmailAddress::fromString($email ?? self::DEFAULT_EMAIL),
            passwordHash: PasswordHash::fromString($passwordHash ?? self::DEFAULT_PASSWORD_HASH),
            roles: RoleCollection::fromStrings($roles ?? [RoleEnum::User->value]),
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
    ): UserAccount {
        return $this->userAccountFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            email: $email ?? $this->faker->unique()->safeEmail(),
            passwordHash: $passwordHash ?? self::DEFAULT_PASSWORD_HASH,
            roles: $roles ?? [RoleEnum::User->value],
        );
    }

    /**
     * @return UserAccount[]
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
