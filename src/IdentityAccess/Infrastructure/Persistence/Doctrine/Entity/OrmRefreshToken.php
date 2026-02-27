<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\RefreshToken\AccountType;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\CreatedAtEntityTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
#[ORM\UniqueConstraint(name: 'uniq_refresh_tokens_token', columns: ['token'])]
class OrmRefreshToken
{
    use CreatedAtEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: TokenHash::MAX_LENGTH)]
    public ?string $token = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $accountUlid = null;

    #[ORM\Column(type: AccountType::NAME)]
    public ?IdentityTypeEnum $accountType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?DateTimeImmutable $expiresAt = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
