<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Entity;

use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\SoftDeleteableEntityTrait;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'customer_profiles')]
#[ORM\UniqueConstraint(name: 'uniq_customer_profiles_user_ulid', columns: ['user_ulid'])]
class OrmCustomerProfile
{
    use TimestampableEntityTrait;
    use SoftDeleteableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $userUlid = null;

    #[ORM\Column(type: Types::STRING, length: FirstName::MAX_LENGTH, nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: LastName::MAX_LENGTH, nullable: true)]
    public ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, length: PhoneNumber::MAX_LENGTH, nullable: true)]
    public ?string $phoneNumber = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
