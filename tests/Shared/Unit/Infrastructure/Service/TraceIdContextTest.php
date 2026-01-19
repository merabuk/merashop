<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Service\TraceIdContext;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TraceIdContextTest extends KernelTestCase
{
    /**
     * @throws InvalidTraceIdException
     */
    public function testContextReset(): void
    {
        $factory = self::getContainer()->get(TraceIdFactoryInterface::class);
        $uuidGenerator = self::getContainer()->get(UuidGeneratorInterface::class);

        $context = new TraceIdContext($factory);

        $id = TraceId::fromString($uuidGenerator->nextV7());
        $context->set($id);

        $context->reset();

        self::assertNotEquals($id->value(), $context->get()->value());
    }
}
