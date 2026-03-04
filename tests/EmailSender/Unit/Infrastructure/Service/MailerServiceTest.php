<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Infrastructure\Mailer\MailerFactoryInterface;
use App\EmailSender\Infrastructure\Mailer\MailerInterface;
use App\EmailSender\Infrastructure\Service\MailerService;
use PHPUnit\Framework\TestCase;

final class MailerServiceTest extends TestCase
{
    private MailerFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(MailerFactoryInterface::class);
    }

    public function testItProcessesEmailCorrectly(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $email = $this->createMock(OutboxEmail::class);

        $driverEnum = DriverEnum::Smtp;
        $driverVo = Driver::fromEnum($driverEnum);

        $email->method('getDriver')->willReturn($driverVo);

        $this->factory->expects(self::once())
            ->method('make')
            ->with(self::equalTo($driverEnum))
            ->willReturn($mailer);

        $mailer->expects(self::once())->method('send')->with($email);

        $this->createService()->process($email);
    }

    private function createService(): MailerService
    {
        return new MailerService(factory: $this->factory);
    }
}
