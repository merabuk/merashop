<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\PasswordGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exceptions\InvalidCredentialsException;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\Shared\Domain\Enum\IdentityTypeEnum;

readonly class AdminPasswordGrantAccountProvider implements PasswordGrantAccountProviderInterface
{
    public function __construct(
        private AdminAccountReadRepositoryInterface $readRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return self::getAccountType()->value;
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function handle(string $username, string $password): GrantResultData
    {
        try {
            $admin = $this->readRepository->findByEmail(EmailAddress::fromString($username));

            if (
                null === $admin
                || !$this->passwordHasher->verify(
                    hashedPassword: $admin->getPasswordHash()->value(),
                    plainPassword: $password
                )
            ) {
                throw new InvalidCredentialsException('Invalid credentials');
            }

            return new GrantResultData(
                subjectUlid: $admin->getUlid()->value(),
                subjectType: self::getAccountType(),
                roles: $admin->getRoles()->toStrings()
            );
        } catch (InvalidIdentityAccessValueObjectException $e) {
            throw new InvalidCredentialsException(message: 'Failed to process admin credentials', previous: $e);
        }
    }

    private static function getAccountType(): IdentityTypeEnum
    {
        return IdentityTypeEnum::Admin;
    }
}
