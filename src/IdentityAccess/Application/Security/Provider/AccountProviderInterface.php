<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider;

use App\IdentityAccess\Application\DTO\GrantResultData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('identity_access.account_provider')]
interface AccountProviderInterface
{
    public static function getDefaultIndexName(): string;

    public function handle(string $username, string $password): GrantResultData;
}
