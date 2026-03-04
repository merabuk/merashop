<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Infrastructure\Mailer\LogMailer;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LogMailerTest extends TestCase
{
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(DriverEnum::Log->value, $this->createMailer()::getDefaultIndexName());
    }

    public function testItLogsMessage(): void
    {
        $outboxEmail = OutboxEmailMother::createWithData();

        $this->logger->expects(self::once())
            ->method('info')
            ->with(
                self::equalTo('Simulating email sending'),
                self::logicalAnd(
                    self::arrayHasKey('id'),
                    self::arrayHasKey('to'),
                    self::arrayHasKey('subject'),
                    self::arrayHasKey('trace_id'),
                )
            );

        $result = $this->createMailer()->send($outboxEmail);

        self::assertNull($result);
    }

    private function createMailer(): LogMailer
    {
        return new LogMailer(logger: $this->logger);
    }
}
