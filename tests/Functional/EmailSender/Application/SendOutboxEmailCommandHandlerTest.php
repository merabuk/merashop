<?php

declare(strict_types=1);

namespace App\Tests\Functional\EmailSender\Application;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommandHandler;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SendOutboxEmailCommandHandlerTest extends KernelTestCase
{
    public function testHandleIncrementsAttemptsOnMailerFailure(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $mailer = $this->createMock(MailerServiceInterface::class);
        $mailer->method('process')->willThrowException(new \Exception('SMTP Timeout'));
        $container->set(MailerServiceInterface::class, $mailer);

        // 2. Создаем письмо в БД (через репозиторий или фикстуры)
        $repository = $container->get(OutboxEmailWriteRepositoryInterface::class);
        $email = $this->createAndSaveEmail($repository);

        $handler = $container->get(SendOutboxEmailCommandHandler::class);
        $handler(new SendOutboxEmailCommand($email->getId()->value()));

        $updatedEmail = $container->get(OutboxEmailReadRepositoryInterface::class)
            ->findById($email->getId()->value());

        $this->assertEquals(1, $updatedEmail->getAttempts()->value());
        $this->assertTrue($updatedEmail->getStatus()->isFailed());
        $this->assertNotNull($updatedEmail->getScheduledAt());
    }
}
