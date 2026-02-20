<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @mixin KernelTestCase
 */
trait DbPerformanceTrait
{
    /**
     * Checks the number of SELECT queries executed for a specific connection
     */
    protected function assertSelectCountLessThanOrEqual(
        int $expectedMax,
        KernelBrowser $client,
        string $connectionName = 'default',
        string $message = ''
    ): void {
        $profile = $client->getProfile();

        if (false === $profile) {
            Assert::fail('The Profiler is disabled. Make sure you called $client->enableProfiler() before the request');
        }

        /** @var DoctrineDataCollector $dbCollector */
        $dbCollector = $profile->getCollector('db');
        $queries = $dbCollector->getQueries()[$connectionName] ?? [];

        $selectQueries = array_filter(
            $queries,
            function (array $q) {
                $isSelect = str_starts_with(ltrim($q['sql'], '" '), 'SELECT');
                if (!$isSelect) {
                    return false;
                }

                foreach ($q['backtrace'] ?? [] as $step) {
                    if (is_a($step['class'] ?? '', HttpKernel::class, true)) {
                        return true;
                    }
                }

                return false;
            }
        );

        $actualCount = count($selectQueries);
        $defaultMessage = sprintf(
            'Possible N+1 detected on connection "%s"! Expected at most %d SELECT queries, but got %d.',
            $connectionName,
            $expectedMax,
            $actualCount
        );

        Assert::assertLessThanOrEqual($expectedMax, $actualCount, $message ?: $defaultMessage);
    }
}
