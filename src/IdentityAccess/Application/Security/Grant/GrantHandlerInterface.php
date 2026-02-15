<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('identity_access.grant_handler')]
interface GrantHandlerInterface
{
    public static function getDefaultIndexName(): string;

    /**
     * @throws GrantHandlerException
     */
    public function handle(OAuth2Data $data): TokenResponseData;
}
