<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Service\OutboxEmailRelayService;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class OutboxEmailRelayServiceTest extends BaseUnitTest
{
    private OutboxEmailReadRepositoryInterface&MockObject $readRepository;
    private MessageBusInterface&MockObject $commandBus;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(OutboxEmailReadRepositoryInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
    }

    public function testItExecutesRelayCommand(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $subMinutes = 2;
        $limit = 10;
        $foundCount = 5;

        $emails = [];
        for ($i = 1; $i <= $foundCount; ++$i) {
            $emails[] = OutboxEmailMother::makeCreatedEmail(id: $i);
        }

        $this->readRepository->expects(self::once())
            ->method('findReadyToProcess')
            ->with(
                self::equalTo($limit),
                self::equalTo($clock->now()),
                self::equalTo($clock->now()->modify(sprintf('-%d minutes', $subMinutes)))
            )
            ->willReturn($emails);

        $this->commandBus->expects(self::exactly($foundCount))
            ->method('dispatch')
            ->with(self::callback(function (SendOutboxEmailCommand $command) use (&$callCount): bool {
                ++$callCount;

                return $command->id === $callCount;
            }))
            ->willReturn(new Envelope(new stdClass()));

        $result = $this->createService($subMinutes, $limit, $clock)->execute();

        self::assertSame($foundCount, $result);
    }

    private function createService(
        int $subMinutes = 10,
        int $limit = 100,
        ?ClockInterface $clock = null,
    ): OutboxEmailRelayService {
        $clock ??= new MockClock();

        return new OutboxEmailRelayService(
            readRepository: $this->readRepository,
            commandBus: $this->commandBus,
            clock: $clock,
            subMinutes: $subMinutes,
            limit: $limit
        );
    }
}
