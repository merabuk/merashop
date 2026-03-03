<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\EventHandler;

use App\EmailSender\Application\EventHandler\UserRegisteredHandler;
use App\EmailSender\Application\Service\EmailQueueServiceInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use PHPUnit\Framework\TestCase;

class UserRegisteredHandlerTest extends TestCase
{
    private EmailQueueServiceInterface $notificationService;

    public function setUp(): void
    {
        $this->notificationService = $this->createMock(EmailQueueServiceInterface::class);
    }

    public function testIsInvokesNotificationServiceCorrectly(): void
    {
        $event = new UserRegisteredSharedEvent(
            id: 'user_ulid',
            email: 'test@example.com'
        );

        $this->notificationService->expects($this->once())
            ->method('queueEmail')
            ->with(
                self::equalTo($event->getRoutingKey()),
                self::equalTo($event->email),
                self::logicalAnd(
                    self::arrayHasKey('appName'),
                    self::arrayHasKey('userName')
                )
            );

        $this->createHandler()($event);
    }

    private function createHandler(): UserRegisteredHandler
    {
        return new UserRegisteredHandler(
            notificationService: $this->notificationService,
            appName: 'Test MeraShop'
        );
    }
}
