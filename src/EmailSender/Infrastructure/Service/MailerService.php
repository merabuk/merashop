<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\EmailSender\Infrastructure\Mailer\MailerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final readonly class MailerService implements MailerServiceInterface
{
    public function __construct(
        private MailerFactory $mailerFactory,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(OutboxEmail $email): void
    {
        $this->mailerFactory->make($email->getDriver()->value())->send($email);
    }
}
