<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\SoftDeleteableEntityTrait;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'module_accounts')]
#[ORM\UniqueConstraint(name: 'uniq_module_accounts_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_module_accounts_client_id', columns: ['client_id'])]
class OrmModuleAccount
{
    use TimestampableEntityTrait;
    use SoftDeleteableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: ClientId::MAX_LENGTH)]
    public ?string $clientId = null;

    #[ORM\Column(type: Types::STRING, length: ClientSecretHash::MAX_LENGTH)]
    public ?string $clientSecret = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $scopes = [];

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
