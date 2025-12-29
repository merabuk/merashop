<?php

declare(strict_types=1);

namespace App\Tests\Functional\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Service\TraceIdContext;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TraceIdContextTest extends KernelTestCase
{
    public function testContextReset(): void
    {
        $factory = self::getContainer()->get(TraceIdFactoryInterface::class);
        $uuidGenerator = self::getContainer()->get(UuidGeneratorInterface::class);

        $context = new TraceIdContext($factory);

        $id = TraceId::fromString($uuidGenerator->nextV7());
        $context->set($id);

        $context->reset();

        $this->assertNotEquals($id->value(), $context->get()->value());
    }
}
