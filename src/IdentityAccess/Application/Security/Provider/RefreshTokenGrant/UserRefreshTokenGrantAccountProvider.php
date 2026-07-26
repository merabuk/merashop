<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Helpers\TypeCastingTrait;

final readonly class UserRefreshTokenGrantAccountProvider implements RefreshTokenGrantAccountProviderInterface
{
    use TypeCastingTrait;

    public function __construct(
        private UserAccountReadRepositoryInterface $readRepository,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return self::getAccountType()->value;
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function handle(string $accountUlid): ?GrantResultData
    {
        try {
            $user = $this->readRepository->findByUlid(Ulid::fromString($accountUlid));

            if (null === $user) {
                return null;
            }

            return new GrantResultData(
                subjectUlid: self::castToNonEmptyString(
                    string: $user->getUlid()->value(),
                    message: 'Giving user ulid is empty'
                ),
                subjectType: self::getAccountType(),
                roles: $user->getRoles()->toStrings(),
                scopes: [],
            );
        } catch (InvalidIdentityAccessValueObjectException $e) {
            throw new InvalidCredentialsException(message: 'Failed to process user refresh token', previous: $e);
        }
    }

    private static function getAccountType(): IdentityTypeEnum
    {
        return IdentityTypeEnum::User;
    }
}
