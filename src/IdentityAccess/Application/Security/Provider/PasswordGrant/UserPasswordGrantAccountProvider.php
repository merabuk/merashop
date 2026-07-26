<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\PasswordGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Helpers\TypeCastingTrait;

final readonly class UserPasswordGrantAccountProvider implements PasswordGrantAccountProviderInterface
{
    use TypeCastingTrait;

    public function __construct(
        private UserAccountReadRepositoryInterface $readRepository,
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
            $user = $this->readRepository->findByEmail(EmailAddress::fromString($username));

            if (
                null === $user
                || !$this->passwordHasher->verify(
                    hashedPassword: $user->getPasswordHash()->value(),
                    plainPassword: $password
                )
            ) {
                throw new InvalidCredentialsException('Invalid credentials');
            }

            return new GrantResultData(
                subjectUlid: self::castToNonEmptyString(
                    string: $user->getUlid()->value(),
                    message: 'Giving user ulid is empty'
                ),
                subjectType: self::getAccountType(),
                roles: $user->getRoles()->toStrings()
            );
        } catch (InvalidIdentityAccessValueObjectException $e) {
            throw new InvalidCredentialsException(message: 'Failed to process user credentials', previous: $e);
        }
    }

    private static function getAccountType(): IdentityTypeEnum
    {
        return IdentityTypeEnum::User;
    }
}
