<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @mixin TestCase
 */
trait ResolverTrait
{
    /**
     * @param class-string $type
     * @param object[]     $attributes
     */
    private function makeArgumentMetadata(
        string $name,
        string $type,
        bool $isNullable = false,
        array $attributes = [],
    ): ArgumentMetadata {
        return new ArgumentMetadata(
            name: $name,
            type: $type,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            isNullable: $isNullable,
            attributes: $attributes
        );
    }
}
