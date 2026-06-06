<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @mixin WebTestCase
 */
trait BaseUriTrait
{
    protected function getBaseUrl(string $routeName, array $params = []): string
    {
        return static::getContainer()->get('router')->generate($routeName, $params);
    }
}
