<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\EventHandler;

use App\EmailSender\Application\EventHandler\AdminCreatedHandler;
use App\EmailSender\Application\Service\EmailQueueServiceInterface;
use App\Shared\Domain\Event\AdminCreatedSharedEvent;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;

final class AdminCreatedHandlerTest extends BaseUnitTest
{
    private EmailQueueServiceInterface&MockObject $notificationService;

    public function setUp(): void
    {
        $this->notificationService = $this->createMock(EmailQueueServiceInterface::class);
    }

    public function testIsInvokesNotificationServiceCorrectly(): void
    {
        $event = new AdminCreatedSharedEvent(
            id: 'admin_ulid',
            email: 'test@example.com',
            temporaryPassword: 'temp123'
        );

        $this->notificationService->expects($this->once())
            ->method('queueEmail')
            ->with(
                self::equalTo($event->getRoutingKey()),
                self::equalTo($event->email),
                self::logicalAnd(
                    self::arrayHasKey('appName'),
                    self::arrayHasKey('adminName'),
                    self::arrayHasKey('temporaryPassword')
                )
            );

        $this->createHandler()($event);
    }

    private function createHandler(): AdminCreatedHandler
    {
        return new AdminCreatedHandler(
            notificationService: $this->notificationService,
            appName: 'Test MeraShop'
        );
    }
}
