<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('identity_access.auth_subject_loader')]
interface AuthSubjectLoaderInterface
{
    public function load(string $ulid): ?AuthSubject;

    public static function getDefaultIndexName(): string;
}
