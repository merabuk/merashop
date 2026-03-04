<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\EmailSender\Infrastructure\Exception\MailerFactoryException;
use App\EmailSender\Infrastructure\Mailer\MailerFactoryInterface;

final readonly class MailerService implements MailerServiceInterface
{
    public function __construct(
        private MailerFactoryInterface $factory,
    ) {
    }

    /**
     * @throws MailerFactoryException
     */
    public function process(OutboxEmail $email): void
    {
        $this->factory->make($email->getDriver()->value())->send($email);
    }
}
