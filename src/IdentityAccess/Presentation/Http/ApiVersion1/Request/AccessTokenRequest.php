<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Request;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
final readonly class AccessTokenRequest implements GroupSequenceProviderInterface
{
    private const string BASE_GROUP = 'AccessTokenRequest';

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(
            callback: 'getGrantTypes',
            message: 'identity_access.grant_type.invalid'
        )]
        public ?string $grant_type,

        #[Assert\NotBlank(groups: [GrantTypeEnum::Password->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::RefreshToken->value])]
        public ?string $username = null,
        #[Assert\NotBlank(groups: [GrantTypeEnum::Password->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::RefreshToken->value])]
        public ?string $password = null,

        #[Assert\NotBlank(groups: [GrantTypeEnum::RefreshToken->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::Password->value])]
        public ?string $refresh_token = null,
    ) {
    }

    public function getData(): OAuth2Data
    {
        return new OAuth2Data(
            grantType: (string) $this->grant_type,
            accountType: IdentityTypeEnum::User,
            username: $this->username,
            password: $this->password,
            refreshToken: $this->refresh_token,
        );
    }

    public function getGroupSequence(): array
    {
        $groups = [self::BASE_GROUP];

        if ($type = GrantTypeEnum::tryFrom((string) $this->grant_type)) {
            $groups[] = $type->value;
        }

        return $groups;
    }

    /**
     * @return string[]
     */
    public static function getGrantTypes(): array
    {
        return [
            GrantTypeEnum::Password->value,
            GrantTypeEnum::RefreshToken->value,
        ];
    }
}
