<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\AdminAccount\StatusType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'admin_accounts')]
#[ORM\UniqueConstraint(name: 'uniq_admin_accounts_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_admin_accounts_email', columns: ['email'])]
class OrmAdminAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME, unique: true)]
    public string $ulid;

    #[ORM\Column(type: Types::STRING, length: 180)]
    public string $email;

    #[ORM\Column(type: Types::STRING)]
    public string $password;

    #[ORM\Column(type: Types::STRING, length: 100)]
    public string $firstName;

    #[ORM\Column(type: Types::STRING, length: 100)]
    public string $lastName;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $roles = [];

    #[ORM\Column(type: StatusType::NAME, enumType: StatusEnum::class)]
    public StatusEnum $status;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
