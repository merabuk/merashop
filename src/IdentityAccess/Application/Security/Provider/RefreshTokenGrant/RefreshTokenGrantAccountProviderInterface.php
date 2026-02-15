<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('identity_access.account_provider.refresh_token_grant')]
interface RefreshTokenGrantAccountProviderInterface
{
    public static function getDefaultIndexName(): string;

    public function handle(string $accountUlid): ?GrantResultData;
}
