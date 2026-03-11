<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Image;

use App\Shared\Domain\Enum\MimeTypeEnum;
use App\Shared\Domain\Service\Image\ImageConstraintsProviderInterface;
use App\Shared\Domain\ValueObject\File\ImageConstraints;
use App\Shared\Infrastructure\Service\Image\ImageConstraintsRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

final class ImageConstraintsRegistryTest extends TestCase
{
    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testItGetsConstraintsCorrectly(): void
    {
        $context = 'valid-context';

        $constraints = $this->getConstraints();

        $provider = $this->createMock(ImageConstraintsProviderInterface::class);
        $provider->method('getConstraints')->willReturn($constraints);

        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($context))
            ->willReturn(true);

        $this->container->expects(self::once())
            ->method('get')
            ->with(self::equalTo($context))
            ->willReturn($provider);

        $result = $this->createRegistry()->getConstraints($context);

        self::assertSame($constraints, $result);
    }

    public function testItReturnsDefaultConstraintsIfConstraintsProviderNotExist(): void
    {
        $context = 'non-existent-context';

        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($context))
            ->willReturn(false);

        $this->container->expects(self::never())->method('get');

        $registry = $this->createRegistry();
        $result = $registry->getConstraints($context);

        self::assertSame($registry::DEFAULT_MAX_SIZE, $result->maxSize);
        self::assertSame($registry::DEFAULT_MIMES, $result->allowedMimeTypes);
        self::assertSame($registry::DEFAULT_MIN_DIMENSION, $result->minWidth);
        self::assertSame($registry::DEFAULT_MIN_DIMENSION, $result->minHeight);
        self::assertSame($registry::DEFAULT_MAX_DIMENSION, $result->maxWidth);
        self::assertSame($registry::DEFAULT_MAX_DIMENSION, $result->maxHeight);
    }

    public function testItReturnsDefaultsConstraintsWhenConstraintsProviderHasWrongType(): void
    {
        $context = 'wrong-type-context';

        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($context))
            ->willReturn(true);

        $this->container->expects(self::once())
            ->method('get')
            ->with(self::equalTo($context))
            ->willReturn(new stdClass());

        $registry = $this->createRegistry();
        $result = $registry->getConstraints($context);

        self::assertSame($registry::DEFAULT_MAX_SIZE, $result->maxSize);
        self::assertSame($registry::DEFAULT_MIMES, $result->allowedMimeTypes);
        self::assertSame($registry::DEFAULT_MIN_DIMENSION, $result->minWidth);
        self::assertSame($registry::DEFAULT_MIN_DIMENSION, $result->minHeight);
        self::assertSame($registry::DEFAULT_MAX_DIMENSION, $result->maxWidth);
        self::assertSame($registry::DEFAULT_MAX_DIMENSION, $result->maxHeight);
    }

    private function getConstraints(): ImageConstraints
    {
        return new ImageConstraints(
            maxSize: 2 * 1024 * 1024, // 2MB,
            allowedMimeTypes: [
                MimeTypeEnum::Png->value,
            ],
            minWidth: 100,
            minHeight: 100,
            maxWidth: 1000,
            maxHeight: 1000
        );
    }

    private function createRegistry(): ImageConstraintsRegistry
    {
        return new ImageConstraintsRegistry($this->container);
    }
}
