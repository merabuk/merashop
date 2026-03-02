<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\ClientCredentialsGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('identity_access.account_provider.client_credentials_grant')]
interface ClientCredentialsGrantAccountProviderInterface
{
    public static function getDefaultIndexName(): string;

    public function handle(string $clientId, string $clientSecret): GrantResultData;
}
