<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\ImageConstraints;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shared.image_constraints_provider')]
interface ImageConstraintsProviderInterface
{
    public static function getDefaultIndexName(): string;

    public function getConstraints(): ImageConstraints;
}
