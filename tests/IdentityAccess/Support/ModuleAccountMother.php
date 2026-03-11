<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Factory\Contract\ModuleAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Id;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Generator;

final readonly class ModuleAccountMother
{
    public const string DEFAULT_ULID = '01KJ815J5HDCTV5A07W8AWKWAH';
    public const string DEFAULT_CLIENT_ID = 'module_client_id';
    private const string DEFAULT_PASSWORD_HASH = '$2y$13$EfTxMGbVX8xxscagFCXKVOHsSKEelbmVsbGX1otIsWzGIZ1cG1uTa'; // password

    public function __construct(
        private ModuleAccountFactoryInterface $moduleAccountFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    /**
     * @param ?string[] $scopes
     *
     * @throws InvalidIdentityAccessValueObjectException
     */
    public static function createWithData(
        ?string $ulid = null,
        ?string $clientId = null,
        ?string $clientSecretHash = null,
        ?array $scopes = null,
        ?int $id = null,
    ): ModuleAccount {
        return new ModuleAccount(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            clientId: ClientId::fromString($clientId ?? self::DEFAULT_CLIENT_ID),
            clientSecret: ClientSecretHash::fromString($clientSecretHash ?? self::DEFAULT_PASSWORD_HASH),
            scopes: ScopeCollection::fromStrings($scopes ?? [RoleEnum::Module->value]),
            id: $id ? Id::fromInt($id) : null
        );
    }

    /**
     * @param ?string[] $scopes
     */
    public function create(
        ?string $ulid = null,
        ?string $clientId = null,
        ?string $clientSecretHash = null,
        ?array $scopes = null,
    ): ModuleAccount {
        return $this->moduleAccountFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            clientId: $clientId ?? $this->faker->unique()->safeEmail(),
            clientSecretHash: $clientSecretHash ?? self::DEFAULT_PASSWORD_HASH,
            scopes: $scopes ?? [RoleEnum::Module->value],
        );
    }

    /**
     * @return ModuleAccount[]
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
