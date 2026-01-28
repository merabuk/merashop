<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Request;

use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
final readonly class AccessTokenRequest implements GroupSequenceProviderInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(
            callback: 'getGrantTypes',
            message: 'auth.grant_type.invalid'
        )]
        public ?string $grant_type,

        #[Assert\NotBlank(groups: [GrantTypeEnum::Password->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::ClientCredentials->value, GrantTypeEnum::RefreshToken->value])]
        public ?string $username = null,
        #[Assert\NotBlank(groups: [GrantTypeEnum::Password->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::ClientCredentials->value, GrantTypeEnum::RefreshToken->value])]
        public ?string $password = null,

        #[Assert\NotBlank(groups: [GrantTypeEnum::ClientCredentials->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::Password->value, GrantTypeEnum::RefreshToken->value])]
        public ?string $client_id = null,
        #[Assert\NotBlank(groups: [GrantTypeEnum::ClientCredentials->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::Password->value, GrantTypeEnum::RefreshToken->value])]
        public ?string $client_secret = null,

        #[Assert\NotBlank(groups: [GrantTypeEnum::RefreshToken->value])]
        #[Assert\Blank(groups: [GrantTypeEnum::Password->value, GrantTypeEnum::ClientCredentials->value])]
        public ?string $refresh_token = null,
    ) {
    }

    public function getGroupSequence(): array
    {
        $groups = ['AccessTokenRequest'];

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
            GrantTypeEnum::ClientCredentials->value,
            GrantTypeEnum::RefreshToken->value,
        ];
    }
}
