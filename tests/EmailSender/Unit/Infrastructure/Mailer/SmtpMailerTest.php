<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Infrastructure\Mailer\SmtpMailer;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Email;

final class SmtpMailerTest extends TestCase
{
    private SymfonyMailerInterface $symfonyMailer;

    protected function setUp(): void
    {
        $this->symfonyMailer = $this->createMock(SymfonyMailerInterface::class);
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(DriverEnum::Smtp->value, $this->createMailer()::getDefaultIndexName());
    }

    public function testItSendsEmail(): void
    {
        $outboxEmail = OutboxEmailMother::createWithData();

        $this->symfonyMailer->expects(self::once())
            ->method('send')
            ->with(self::isInstanceOf(Email::class));

        $result = $this->createMailer()->send($outboxEmail);

        self::assertNull($result);
    }

    private function createMailer(): SmtpMailer
    {
        return new SmtpMailer(symfonyMailer: $this->symfonyMailer);
    }
}
