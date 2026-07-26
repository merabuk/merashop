<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Helpers\TypeCastingTrait;

final readonly class AdminRefreshTokenGrantAccountProvider implements RefreshTokenGrantAccountProviderInterface
{
    use TypeCastingTrait;

    public function __construct(
        private AdminAccountReadRepositoryInterface $readRepository,
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
            $admin = $this->readRepository->findByUlid(Ulid::fromString($accountUlid));

            if (null === $admin) {
                return null;
            }

            return new GrantResultData(
                subjectUlid: self::castToNonEmptyString(
                    string: $admin->getUlid()->value(),
                    message: 'Giving admin ulid is empty'
                ),
                subjectType: self::getAccountType(),
                roles: $admin->getRoles()->toStrings()
            );
        } catch (InvalidIdentityAccessValueObjectException $e) {
            throw new InvalidCredentialsException(message: 'Failed to process admin refresh token', previous: $e);
        }
    }

    private static function getAccountType(): IdentityTypeEnum
    {
        return IdentityTypeEnum::Admin;
    }
}
