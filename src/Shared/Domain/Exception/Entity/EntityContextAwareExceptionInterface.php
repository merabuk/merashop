<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Entity;

interface EntityContextAwareExceptionInterface
{
    public static function getEntityNameKey(): string;
}
