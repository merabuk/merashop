<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'identity_access_module_accounts')]
class OrmModuleAccount
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME, unique: true)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: ClientId::MAX_LENGTH, unique: true)]
    public ?string $clientId = null;

    #[ORM\Column(type: Types::STRING, length: ClientSecretHash::MAX_LENGTH)]
    public ?string $clientSecret = null;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $scopes = [];

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
