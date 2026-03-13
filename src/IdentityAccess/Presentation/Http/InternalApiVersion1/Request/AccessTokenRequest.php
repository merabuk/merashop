<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\InternalApiVersion1\Request;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
final readonly class AccessTokenRequest implements GroupSequenceProviderInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(
            callback: 'getGrantTypes',
            message: 'identity_access.grant_type_invalid'
        )]
        public ?string $grant_type,

        #[Assert\NotBlank(groups: [GrantTypeEnum::ClientCredentials->value])]
        public ?string $client_id = null,
        #[Assert\NotBlank(groups: [GrantTypeEnum::ClientCredentials->value])]
        public ?string $client_secret = null,
    ) {
    }

    public function getData(): OAuth2Data
    {
        return new OAuth2Data(
            grantType: (string) $this->grant_type,
            accountType: IdentityTypeEnum::Module,
            clientId: $this->client_id,
            clientSecret: $this->client_secret,
        );
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
            GrantTypeEnum::ClientCredentials->value,
        ];
    }
}
