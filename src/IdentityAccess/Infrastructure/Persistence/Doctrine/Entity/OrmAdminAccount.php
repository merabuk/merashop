<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\AdminAccount\StatusType;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\SoftDeleteableEntityTrait;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'admin_accounts')]
#[ORM\UniqueConstraint(name: 'uniq_admin_accounts_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_admin_accounts_email', columns: ['email'])]
class OrmAdminAccount
{
    use TimestampableEntityTrait;
    use SoftDeleteableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: EmailAddress::MAX_LENGTH)]
    public ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: PasswordHash::MAX_LENGTH)]
    public ?string $passwordHash = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $roles = [];

    #[ORM\Column(type: StatusType::NAME)]
    public ?StatusEnum $status = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $passwordChangedAt = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
