<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'user_accounts')]
#[ORM\UniqueConstraint(name: 'uniq_user_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class OrmUserAccount
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: EmailAddress::MAX_LENGTH)]
    public ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: PasswordHash::MAX_LENGTH)]
    public ?string $passwordHash = null;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $roles = [];

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
