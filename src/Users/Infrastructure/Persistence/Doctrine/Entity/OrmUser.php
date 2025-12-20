<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Entity;

use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Table(name: 'users_user')]
class OrmUser
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME, unique: true)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: FirstName::MAX_LENGTH)]
    public ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: LastName::MAX_LENGTH)]
    public ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, length: EmailAddress::MAX_LENGTH)]
    public ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: PhoneNumber::MAX_LENGTH, nullable: true)]
    public ?string $phoneNumber = null;

    #[ORM\Column(type: Types::STRING, length: PasswordHash::MAX_LENGTH)]
    public ?string $password = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
