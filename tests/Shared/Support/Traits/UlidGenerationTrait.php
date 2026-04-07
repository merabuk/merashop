<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait UlidGenerationTrait
{
    protected UlidGeneratorInterface&MockObject $ulidGenerator;

    protected function setUlidGenerator(): void
    {
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
    }

    protected function expectGenerateUlid(string $expectedUlid): void
    {
        $this->ulidGenerator->expects(self::once())
            ->method('next')
            ->willReturn($expectedUlid);
    }

    /**
     * @param string[] $ulids
     */
    protected function expectGenerateManyUlids(array $ulids): void
    {
        $this->ulidGenerator->expects(self::exactly(count($ulids)))
            ->method('next')
            ->willReturnOnConsecutiveCalls(...$ulids);
    }

    protected function generateUlidNeverCalled(): void
    {
        $this->ulidGenerator->expects(self::never())->method('next');
    }
}
