<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Infrastructure\Exception\MailerFactoryException;
use App\EmailSender\Infrastructure\Mailer\MailerFactory;
use App\EmailSender\Infrastructure\Mailer\MailerInterface;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use stdClass;

final class MailerFactoryTest extends BaseUnitTest
{
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testItMakesMailer(): void
    {
        $driver = DriverEnum::Smtp;

        $mailer = $this->createMock(MailerInterface::class);

        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($driver->value))
            ->willReturn(true);
        $this->container->expects(self::once())
            ->method('get')
            ->with(self::equalTo($driver->value))
            ->willReturn($mailer);

        $this->createFactory()->make($driver);
    }

    public function testThrowsExceptionIfMailerDoesNotExist(): void
    {
        $driver = DriverEnum::Smtp;

        $this->container->method('has')->willReturn(false);

        $this->expectException(MailerFactoryException::class);
        $this->createFactory()->make($driver);
    }

    public function testThrowsExceptionIfMailerIsNotInstanceOfMailerInterface(): void
    {
        $driver = DriverEnum::Smtp;

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn(new stdClass());

        $this->expectException(MailerFactoryException::class);
        $this->createFactory()->make($driver);
    }

    private function createFactory(): MailerFactory
    {
        return new MailerFactory(mailers: $this->container);
    }
}
