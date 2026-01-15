<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'identity_access_refresh_tokens')]
class OrmRefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    public ?string $token = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $accountUlid = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?\DateTimeImmutable $expiresAt = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
