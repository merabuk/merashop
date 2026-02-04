<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Functional\Application\Command\SendOutboxEmail;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommandHandler;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SendOutboxEmailCommandHandlerTest extends KernelTestCase
{
    private OutboxEmailMother $mother;
    private OutboxEmailReadRepositoryInterface $readRepository;
    private OutboxEmailWriteRepositoryInterface $writeRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->mother = $container->get(OutboxEmailMother::class);
        $this->readRepository = $container->get(OutboxEmailReadRepositoryInterface::class);
        $this->writeRepository = $container->get(OutboxEmailWriteRepositoryInterface::class);
    }

    public function testHandleIncrementsAttemptsOnMailerFailure(): void
    {
        $container = self::getContainer();

        $mailer = $this->createMock(MailerServiceInterface::class);
        $mailer->method('process')->willThrowException(new Exception('SMTP Timeout'));
        $container->set(MailerServiceInterface::class, $mailer);

        $email = $this->writeRepository->save($this->mother->createBaseEmail());

        $handler = $container->get(SendOutboxEmailCommandHandler::class);
        $handler(new SendOutboxEmailCommand($email->getId()->value()));

        $updatedEmail = $this->readRepository->findById($email->getId()->value());

        self::assertEquals(1, $updatedEmail->getAttempts()->value());
        self::assertTrue($updatedEmail->getStatus()->isFailed());
        self::assertNotNull($updatedEmail->getScheduledAt());
    }
}
